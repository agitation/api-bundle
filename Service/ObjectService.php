<?php
/**
 * @package    agitation/api
 * @link       http://github.com/agitation/AgitApiBundle
 * @author     Alex Günsche <http://www.agitsol.com/>
 * @copyright  2012-2015 AGITsol GmbH
 * @license    http://opensource.org/licenses/MIT
 */

namespace Agit\ApiBundle\Service;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\ClassMetadataInfo;
use Doctrine\ORM\Proxy\Proxy;
use Agit\CommonBundle\Exception\InternalErrorException;
use Agit\PluggableBundle\Strategy\Cache\CacheLoaderFactory;
use Agit\PluggableBundle\Strategy\ServiceInjectorTrait;
use Agit\IntlBundle\Translate;
use Agit\ApiBundle\Common\AbstractObject;
use Agit\ApiBundle\Exception\InvalidObjectException;
use Agit\ApiBundle\Exception\InvalidObjectValueException;
use Agit\ApiBundle\Annotation\Property\AbstractType;
use Agit\ApiBundle\Annotation\Property\Name;

class ObjectService extends AbstractApiService
{
    use ServiceInjectorTrait;

    protected $container;

    protected $cacheLoader;

    private $entityManager;

    private $objects;

    // reverse mapping (class => object name)
    private $classes;

    // list of entity classes
    private $entities;

    public function __construct(CacheLoaderFactory $cacheLoaderFactory, EntityManager $entityManager, ContainerInterface $container = null)
    {
        $this->cacheLoader = $cacheLoaderFactory->create("agit.api.object");
        $this->entityManager = $entityManager;
        $this->container = $container;

        AbstractType::setValidationService($container->get("agit.validation"));
    }

    public function rawRequestToApiObject($rawRequest, $expectedObject)
    {
        $result = null;

        if (substr($expectedObject, -2) === "[]")
        {
            if (!is_array($rawRequest))
                throw new InvalidObjectException(Translate::t("The request is expected to be an array."));

            $result = [];

            foreach ($rawRequest as $rawRequestElem)
                $result[] = $this->rawRequestToApiObject($rawRequestElem, substr($expectedObject, 0, -2));
        }
        else
        {
            $meta = $this->getObjectMeta($expectedObject);
            $expectsScalar = $meta->get("Object")->get("isScalar");

            if ($expectsScalar)
            {
                if (!is_scalar($rawRequest))
                    throw new InvalidObjectException(Translate::t("The request is expected to be a scalar value."));

                // we fill the scalar object, but only to see if it passes validation.
                // then we return the bare request
                $object = $this->createObject($expectedObject);
                $object->set("_", $rawRequest);
                $object->validate();

                $result = $rawRequest;
            }
            else
            {
                $result = $this->createObject($expectedObject, $rawRequest);
            }
        }

        return $result;
    }

    public function createObject($objectName, $data = null)
    {
        $this->load();

        $objectMeta = $this->getObjectMeta($objectName);
        $propMetaContainerList = $this->getPropertyMeta($objectName);

        $objectClass = $this->objects[$objectName]["class"];
        $object = new $objectClass($objectMeta, $propMetaContainerList);

        $this->injectServices($object, $objectMeta->get("Object")->get("depends"));

        if (is_object($data))
            $this->fill($object, $data);

        return $object;
    }

    public function getObjectNames()
    {
        $this->load();
        return array_keys($this->objects);
    }

    public function getObjectMeta($objectName)
    {
        $this->load();

        if (!isset($this->objects[$objectName]))
            throw new InvalidObjectException("Invalid object: $objectName");

        return $this->createMetaContainer($this->objects[$objectName]["objectMeta"]);
    }

    public function getPropertyMeta($objectName)
    {
        $this->load();

        if (!isset($this->objects[$objectName]))
            throw new InvalidObjectException("Invalid object: $objectName");

        $metas = [];

        foreach ($this->objects[$objectName]["propMetaList"] as $propName => $propMetaList)
            $metas[$propName] = $this->createMetaContainer($propMetaList);

        return $metas;
    }

    public function getObjectNameFromClass($class)
    {
        if (is_null($this->classes))
        {
            $this->classes = [];

            foreach ($this->getAllMeta() as $objectName => $data)
                $this->classes[$data["class"]] = $objectName;
        }

        if (!isset($this->classes[$class]))
            throw new InternalErrorException("Class `$class` has not been registered.");

        return $this->classes[$class];
    }

    public function fill(AbstractObject $object, $data)
    {
        if (!is_object($data))
            throw new InternalErrorException("The `data` parameter must be an object.");

        if ($this->isEntity($data))
        {
            $this->fillFromEntity($object, $data);
        }
        else
        {
            if ($data instanceof \stdClass)
            {
                $values = get_object_vars($data) + $object->getValues();

                foreach ($values as $key => $value)
                {
                    $type = $object->getPropertyMeta($key, "Type");
                    $object->set($key, $this->createFieldValue($type, $key, $value));
                }
            }

            $object->validate();
        }
    }

    protected function load()
    {
        if (is_null($this->objects))
            $this->objects = $this->cacheLoader->load();
    }

    protected function getAllMeta()
    {
        $this->load();
        return $this->objects;
    }

    protected function fillFromEntity($object, $entity)
    {
        $metadata = $this->entityManager->getClassMetadata(get_class($entity));

        if ($entity instanceof Proxy)
            $entity->__load();

        foreach (array_keys($object->getValues()) as $prop)
        {
            $getter = "get".ucfirst($prop);
            $value = null;

            // check if a getter exists, otherwise access value through metadata
            if (is_callable([$entity, $getter]))
                $value = $entity->$getter();
            elseif ($metadata->hasField($prop) || $metadata->hasAssociation($prop))
                $value = $metadata->getFieldValue($entity, $prop);

            if ($metadata->hasField($prop))
            {
                $object->set($prop, $value);
            }
            elseif ($metadata->hasAssociation($prop))
            {
                $mapping = $metadata->getAssociationMapping($prop);
                $propType = $object->getPropertyMeta($prop, "Type");

                if (!$propType->isObjectType())
                    throw new InternalErrorException(sprintf("Wrong type for the `%s` field of the `%s` object: Must be an object type.", $prop, $object->getObjectName()));

                if ($mapping["type"] & ClassMetadataInfo::TO_ONE)
                {
                    $object->set($prop, $this->createObject($propType->getTargetClass(), $value));
                }
                elseif ($mapping["type"] & ClassMetadataInfo::TO_MANY)
                {
                    // TODO
                }
            }
        }
    }

    protected function getContainer()
    {
        return $this->container;
    }

    protected function isEntity($data)
    {
        $isEntity = false;

        if (is_object($data))
        {
            if (!$this->entities)
                $this->entities = $this->entityManager->getConfiguration()
                    ->getMetadataDriverImpl()->getAllClassNames();

            $className = get_class($data);

            if ($data instanceof Proxy)
                $className = get_parent_class($data);

            $isEntity = in_array($className, $this->entities);
        }

        return $isEntity;
    }

    /**
     * NOTE: This method does only a rough pre-flight validation to avoid runtime errors.
     * Actual in-depth validation happens in the object itself.
     */
    private function createFieldValue($type, $key, $value)
    {
        $result = null;
        $expectedType = $type->getType();

        if (is_scalar($value) || is_null($value) || $expectedType === "polymorphic")
        {
            $result = $value;
        }
        elseif (is_array($value))
        {
            if ($type->isObjectType() && $type->isListType())
            {
                $result = [];

                foreach ($value as $listValue)
                    $result[] = $this->createFieldValue($propMeta, $key, $listValue);
            }
            elseif (in_array($expectedType, ["array", "map", "entity", "entitylist"]))
            {
                $result = $value;
            }
            else
            {
                throw new InvalidObjectValueException(sprintf(Translate::t("Invalid value for the `%s` property."), $key));
            }
        }
        elseif (is_object($value))
        {
            if (!$type->isObjectType())
                throw new InvalidObjectValueException(sprintf(Translate::t("Invalid value for the `%s` property."), $key));

            $result = $this->createObject($type->getTargetClass(), $value);
        }

        return $result;
    }
}
