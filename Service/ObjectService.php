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
use Agit\CommonBundle\Exception\InternalErrorException;
use Agit\PluggableBundle\Strategy\Cache\CacheLoaderFactory;
use Agit\IntlBundle\Translate;
use Agit\ApiBundle\Api\Object\AbstractObject;
use Agit\ApiBundle\Exception\InvalidObjectException;
use Agit\ApiBundle\Exception\InvalidObjectValueException;
use Agit\ApiBundle\Api\Meta\MetaContainer;
use Agit\ApiBundle\Api\Meta\Property\AbstractType;
use Agit\ApiBundle\Api\Meta\Property\Name;

class ObjectService extends AbstractApiService
{
    /**
     * @var service container instance.
     */
    protected $container;

    /**
     * @var CacheLoader instance.
     */
    protected $cacheLoader;

    private $entityService;

    private $objects;

    // reverse mapping (class => object name)
    private $classes;

    public function __construct(CacheLoaderFactory $CacheLoaderFactory, ContainerInterface $container)
    {
        $this->cacheLoader = $CacheLoaderFactory->create("agit.api.object");
        $this->container = $container;

        AbstractType::setValidationService($container->get('agit.validation'));
    }

    public function rawRequestToApiObject($rawRequest, $expectedObject)
    {
        $result = null;

        if (substr($expectedObject, -2) === '[]')
        {
            if (!is_array($rawRequest))
                throw new InvalidObjectException(Translate::t("The request is expected to be an array."));

            $result = [];

            foreach ($rawRequest as $rawRequestElem)
                $result[] = $this->rawRequestToApiObject($rawRequestElem, substr($expectedObject, 0, -2));
        }
        else
        {
            $meta = $this->getMeta($expectedObject);
            $expectsScalar = $this->composeMeta($meta['objectMeta']['Object'])->get('isScalar');

            if ($expectsScalar)
            {
                if (!is_scalar($rawRequest))
                    throw new InvalidObjectException(Translate::t("The request is expected to be a scalar value."));

                // we fill the scalar object, but only to see if it passes validation.
                // then we return the bare request
                $object = $this->createObject($expectedObject);
                $object->set('value', $rawRequest);
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
        if (is_string($data))
            throw new InternalErrorException("ATTENTION: New method signature.");

        $meta = $this->getMeta($objectName);

        $objectMetaContainer = $this->createMetaContainer($meta['objectMeta']);
        $propMetaContainerList = [];

        foreach ($meta['propMetaList'] as $propName => $propMetaList)
            $propMetaContainerList[$propName] = $this->createMetaContainer($propMetaList);

        $objectClass = $meta['class'];
        $object = new $objectClass($this->container, $objectMetaContainer, $propMetaContainerList, $objectName);

        // TODO: Don't pass $objectName as a parameter, instead there should be a Meta carring this

        if (is_object($data))
            $this->fill($object, $data);

        return $object;
    }

    public function getMeta($objectName)
    {
        if (is_null($this->objects))
            $this->objects = $this->cacheLoader->load();

        if (!isset($this->objects[$objectName]))
            throw new InvalidObjectException("Invalid object: $objectName");

        return $this->objects[$objectName];
    }

    public function getMetaList()
    {
        if (is_null($this->objects))
            $this->objects = $this->cacheLoader->load();

        return $this->objects;
    }

    public function getObjectNameFromClass($class)
    {
        if (is_null($this->classes))
        {
            $this->classes = [];

            foreach ($this->getMetaList() as $objectName => $data)
                $this->classes[$data['class']] = $objectName;
        }

        if (!isset($this->classes[$class]))
            throw new InternalErrorException("Class '$class' has not been registered.");

        return $this->classes[$class];
    }

    public function fill(AbstractObject &$object, $data)
    {
        if (!is_object($data))
            throw new InternalErrorException("The 'data' parameter must be an object.");

//         if ($this->entityService->isEntity($data))
//         {
//             $this->fillFromEntity($object, $data);
//         }
//         else
//         {
            if ($data instanceof \stdClass)
            {
                $values = get_object_vars($data) + $object->getValues();

                foreach ($values as $key => $value)
                {
                    $type = $object->getPropertyMeta($key, 'Type');
                    $object->set($key, $this->createFieldValue($type, $key, $value));
                }
            }

            $object->validate();
//         }
    }

    /**
     * NOTE: This method does only a rough pre-flight validation to avoid runtime errors.
     * Actual in-depth validation happens in the object itself.
     */
    private function createFieldValue($type, $key, $value)
    {
        $result = null;
        $expectedType = $type->getType();

        if (is_scalar($value) || is_null($value) || $expectedType === 'polymorphic')
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
            elseif (in_array($expectedType, ['array', 'map', 'entity', 'entitylist']))
            {
                $result = $value;
            }
            else
            {
                throw new InvalidObjectValueException(sprintf(Translate::t("Invalid value for the “%s” property."), $key));
            }
        }
        elseif (is_object($value))
        {
            if (!$propMeta->child)
                throw new InvalidObjectValueException(sprintf(Translate::t("Invalid value for the “%s” property."), $key));

            $result = $this->createObject($propMeta->child->class, $value);
        }

        return $result;
    }
}
