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
use Agit\CoreBundle\Entity\AbstractEntity;
use Agit\CoreBundle\Pluggable\Strategy\Cache\CacheLoader;
use Agit\CoreBundle\Exception\InternalErrorException;
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

    private $translate;

    public function __construct(CacheLoader $cacheLoader, ContainerInterface $container)
    {
        $this->cacheLoader = $cacheLoader;
        $this->container = $container;
        $this->translate = $container->get('agit.intl.translate');

        AbstractType::setValidationService($container->get('agit.validation'));
        AbstractType::setTranslationService($this->translate);
    }

    public function rawRequestToApiObject($rawRequest, $expectedObject)
    {
        $result = null;

        if (substr($expectedObject, -2) === '[]')
        {
            if (!is_array($rawRequest))
                throw new InvalidObjectException($this->translate->t("The request is expected to be an array."));

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
                    throw new InvalidObjectException($this->translate->t("The request is expected to be a scalar value."));

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
            $this->objects = $this->cacheLoader->loadPlugins();

        if (!isset($this->objects[$objectName]))
            throw new InvalidObjectException("Invalid object: $objectName");

        return $this->objects[$objectName];
    }

    public function getMetaList()
    {
        if (is_null($this->objects))
            $this->objects = $this->cacheLoader->loadPlugins();

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
                throw new InvalidObjectValueException(sprintf($this->translate->t("Invalid value for the “%s” property."), $key));
            }
        }
        elseif (is_object($value))
        {
            if (!$propMeta->child)
                throw new InvalidObjectValueException(sprintf($this->translate->t("Invalid value for the “%s” property."), $key));

            $result = $this->createObject($propMeta->child->class, $value);
        }

        return $result;
    }

//     private function fillFromEntity(AbstractObject &$object, AbstractEntity $entity)
//     {
//         foreach (array_keys($object->getValues()) as $key)
//         {
//             $type = $object->getPropertyMeta($key, 'Type');
//
//             $methodName = ($type && $type->getOptions()->source)
//                 ? $type->getOptions()->source
//                 : 'get'.ucfirst($key);
//
//             /*
//                 TODO: Use Type objects instead of guessing
//             */
//
//             if (is_callable([$entity, $methodName]))
//             {
//                 $value = $entity->$methodName();
//
//                 if (is_scalar($value))
//                 {
//                     $object->set($key, $value);
//                 }
//                 elseif ($this->keyIndicatesObjectList($key) && $this->entityService->isEntityCollection($value))
//                 {
//                     $list = [];
//
//                     foreach ($value->getValues() as $val)
//                     {
//                         $objKey = $this->getObjectNameFromListKey($key);
//
//                         if (!$propMeta->child)
//                             throw new InternalErrorException("Class for $objKey is not set.");
//
//                         $list[] = $this->createChildEntityObject($val, $propMeta->child);
//                     }
//
//                     $object->set($key, $list);
//                 }
//                 elseif ($this->keyIndicatesObject($key) && $this->entityService->isEntity($value))
//                 {
//                     if (!$propMeta->child)
//                         throw new InternalErrorException("Class for $key is not set.");
//
//                     $object->set($key, $this->createChildEntityObject($value, $propMeta->child));
//                 }
//                 elseif (is_array($value))
//                 {
//                     $list = [];
//
//                     foreach ($value as $val)
//                         if (is_scalar($val))
//                             $list[] = $val;
//
//                     $object->set($key, $list);
//                 }
//                 elseif (is_object($value) && $propMeta->child)
//                 {
//                     $objectChild = $this->createObject($propMeta->child->class, $value);
//                     $object->set($key, $objectChild);
//                 }
//             }
//         }
//
//         $object->fill($entity);
//         $object->validate();
//     }
// 
//     private function createChildEntityObject($entity, $objClassName)
//     {
//         $entityName = $entity->getEntityClass();
//         return $this->createObject($objClassName->class, $entity);
//     }
}
