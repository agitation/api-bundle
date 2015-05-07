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
    protected $Container;

    /**
     * @var CacheLoader instance.
     */
    protected $CacheLoader;

    private $EntityService;

    private $objects;

    public function __construct(CacheLoader $CacheLoader, ContainerInterface $Container)
    {
        $this->CacheLoader = $CacheLoader;
        $this->Container = $Container;

        AbstractType::setValidationService($Container->get('agit.validation'));
        AbstractType::setTranslationService($Container->get('agit.intl.translate'));
    }

    /**
     * @param namespace namespace of object to create.
     * @param name name of object to create.
     * @param data data to fill the object with. Defaults to null.
     * @param child marks object as child (some objects omit certain properties and hence save resources). Defaults to false.
     * @return AbstractObject
     **/
    public function createObject($objectName, $data = null)
    {
        if (is_string($data))
            throw new InternalErrorException("ATTENTION: New method signature.");

        if (is_null($this->objects))
            $this->objects = $this->CacheLoader->loadPlugins();

        if (!isset($this->objects[$objectName]))
            throw new InvalidObjectException("Invalid object: $objectName");

        $ObjectMetaContainer = $this->createMetaContainer($this->objects[$objectName]['objectMeta']);
        $PropMetaContainerList = [];

        foreach ($this->objects[$objectName]['propMetaList'] as $propName => $propMetaList)
            $PropMetaContainerList[$propName] = $this->createMetaContainer($propMetaList);

        $objectClass = $this->objects[$objectName]['class'];
        $Object = new $objectClass($this->Container, $ObjectMetaContainer, $PropMetaContainerList, $objectName);

        // TODO: Don't pass $objectName as a parameter, instead there should be a Meta carring this

        if (is_object($data))
            $this->fill($Object, $data);

        return $Object;
    }

    public function getObjectNames()
    {
        if (is_null($this->objects))
            $this->objects = $this->CacheLoader->loadPlugins();

        return $this->objects;
    }

    public function fill(AbstractObject &$Object, $data)
    {
        if (!is_object($data))
            throw new InternalErrorException("The 'data' parameter must be an object.");

//         if ($this->EntityService->isEntity($data))
//         {
//             $this->fillFromEntity($Object, $data);
//         }
//         else
//         {
            if ($data instanceof \stdClass)
            {
                $values = get_object_vars($data) + $Object->getValues();

                foreach ($values as $key => $value)
                {
                    $Type = $Object->getPropertyMeta($key, 'Type');
                    $Object->set($key, $this->createFieldValue($Type, $key, $value));
                }
            }

            $Object->validate();
//         }
    }

    /**
     * NOTE: This method does only a rough pre-flight validation to avoid runtime errors.
     * Actual in-depth validation happens in the object itself.
     */
    private function createFieldValue($Type, $key, $value)
    {
        $result = null;
        $expectedType = $Type->getType();

        if (is_scalar($value) || is_null($value) || $expectedType === 'polymorphic')
        {
            $result = $value;
        }
        elseif (is_array($value))
        {
            if ($Type->isObjectType() && $Type->isListType())
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
                throw new InvalidObjectValueException(sprintf("Invalid value for the “%s” property.", $key));
            }
        }
        elseif (is_object($value))
        {
            if (!$propMeta->child)
                throw new InvalidObjectValueException(sprintf("Invalid value for the “%s” property.", $key));

            $result = $this->createObject($propMeta->child->class, $value);
        }

        return $result;
    }

//     private function fillFromEntity(AbstractObject &$Object, AbstractEntity $Entity)
//     {
//         foreach (array_keys($Object->getValues()) as $key)
//         {
//             $Type = $Object->getPropertyMeta($key, 'Type');
//
//             $methodName = ($Type && $Type->getOptions()->source)
//                 ? $Type->getOptions()->source
//                 : 'get'.ucfirst($key);
//
//             /*
//                 TODO: Use Type objects instead of guessing
//             */
//
//             if (is_callable([$Entity, $methodName]))
//             {
//                 $value = $Entity->$methodName();
//
//                 if (is_scalar($value))
//                 {
//                     $Object->set($key, $value);
//                 }
//                 elseif ($this->keyIndicatesObjectList($key) && $this->EntityService->isEntityCollection($value))
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
//                     $Object->set($key, $list);
//                 }
//                 elseif ($this->keyIndicatesObject($key) && $this->EntityService->isEntity($value))
//                 {
//                     if (!$propMeta->child)
//                         throw new InternalErrorException("Class for $key is not set.");
//
//                     $Object->set($key, $this->createChildEntityObject($value, $propMeta->child));
//                 }
//                 elseif (is_array($value))
//                 {
//                     $list = [];
//
//                     foreach ($value as $val)
//                         if (is_scalar($val))
//                             $list[] = $val;
//
//                     $Object->set($key, $list);
//                 }
//                 elseif (is_object($value) && $propMeta->child)
//                 {
//                     $ObjectChild = $this->createObject($propMeta->child->class, $value);
//                     $Object->set($key, $ObjectChild);
//                 }
//             }
//         }
//
//         $Object->fill($Entity);
//         $Object->validate();
//     }
// 
//     private function createChildEntityObject($Entity, $objClassName)
//     {
//         $entityName = $Entity->getEntityClass();
//         return $this->createObject($objClassName->class, $Entity);
//     }
}
