<?php

/*
 * @package    agitation/api-bundle
 * @link       http://github.com/agitation/api-bundle
 * @author     Alexander Günsche
 * @license    http://opensource.org/licenses/MIT
 */

namespace Agit\ApiBundle\Service;

use Agit\ApiBundle\Annotation\Property\AbstractType;
use Agit\ApiBundle\Exception\InvalidObjectException;
use Agit\BaseBundle\Exception\InternalErrorException;
use Agit\ValidationBundle\ValidationService;
use Doctrine\Common\Cache\Cache;

class ObjectMetaService
{
    use MetaAwareTrait;

    private $validationService;

    protected $factory;

    private $objectMetaCache = [];

    private $objectPropMetasCache = [];

    public function __construct(Cache $cache, ValidationService $validationService, Factory $factory = null)
    {
        $this->objects = $cache->fetch("agit.api.object") ?: [];
        $this->factory = $factory;

        AbstractType::setValidationService($validationService);
        AbstractType::setObjectMetaService($this);
    }

    public function createObject($objectName)
    {
        $objectMetas = $this->getObjectMetas($objectName);

        $objectClass = $this->getObjectClass($objectName);
        $deps = $this->composeMeta($this->objects[$objectName]["deps"]);
        $object = $this->factory->create($objectClass, $deps);
        $object->init($objectName, $this);

        return $object;
    }

    public function getObjectNames()
    {
        return array_keys($this->objects);
    }

    public function objectExists($objectName)
    {
        return isset($this->objects[$objectName]);
    }

    public function getObjectClass($objectName)
    {
        if (! isset($this->objects[$objectName])) {
            throw new InvalidObjectException("Invalid object: $objectName");
        }

        return $this->objects[$objectName]["class"];
    }

    public function getDefaultValues($objectName)
    {
        if (! isset($this->objects[$objectName])) {
            throw new InvalidObjectException("Invalid object: $objectName");
        }

        return $this->objects[$objectName]["defaults"];
    }

    public function getObjectMetas($objectName)
    {
        if (! isset($this->objects[$objectName])) {
            throw new InvalidObjectException("Invalid object: $objectName");
        }

        if (! isset($this->objectMetaCache[$objectName])) {
            $this->objectMetaCache[$objectName] = $this->createMetaContainer($this->objects[$objectName]["objectMeta"]);
        }

        return $this->objectMetaCache[$objectName];
    }

    public function getObjectPropertyMetas($objectName)
    {
        if (! isset($this->objects[$objectName])) {
            throw new InvalidObjectException("Invalid object: $objectName");
        }

        if (! isset($this->objectPropMetasCache[$objectName])) {
            $this->objectPropMetasCache[$objectName] = [];

            foreach ($this->objects[$objectName]["propMetaList"] as $propName => $propMetaList) {
                $this->objectPropMetasCache[$objectName][$propName] = $this->createMetaContainer($propMetaList);
            }
        }

        return $this->objectPropMetasCache[$objectName];
    }

    public function getPropertyMetas($objectName, $propName)
    {
        if (! isset($this->objects[$objectName])) {
            throw new InvalidObjectException("Invalid object: $objectName");
        }

        $objectPropsMetas = $this->getObjectPropertyMetas($objectName);

        if (! isset($objectPropsMetas[$propName])) {
            throw new InvalidObjectException("Invalid object property: $objectName.$propName");
        }

        return $objectPropsMetas[$propName];
    }

    public function getPropertyMeta($objectName, $propName, $metaName)
    {
        if (! isset($this->objects[$objectName])) {
            throw new InvalidObjectException("Invalid object: $objectName");
        }

        $propMetas = $this->getPropertyMetas($objectName, $propName);

        if (! $propMetas->has($metaName)) {
            throw new InvalidObjectException("Invalid object property meta: $objectName.$propName.$metaName");
        }

        return $propMetas->get($metaName);
    }

    public function getObjectNameFromClass($class)
    {
        if (is_null($this->classes)) {
            $this->classes = [];

            foreach ($this->objects as $objectName => $data) {
                $this->classes[$data["class"]] = $objectName;
            }
        }

        if (! isset($this->classes[$class])) {
            throw new InternalErrorException("Class `$class` has not been registered.");
        }

        return $this->classes[$class];
    }
}
