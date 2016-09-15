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

    private $cache;

    private $validationService;

    protected $factory;

    public function __construct(Cache $cache, ValidationService $validationService, Factory $factory = null)
    {
        $this->objects = $cache->fetch("agit.api.object") ?: [];
        AbstractType::setValidationService($validationService);
        $this->factory = $factory;
    }

    public function createObject($objectName, $force = false)
    {
        $objectMetas = $this->getObjectMetas($objectName);

        if ($objectMetas->get("Object")->get("super") && ! $force) {
            throw new InternalErrorException(sprintf("Object %s is marked as super class and can therefore not be instantiated.", $objectName));
        }

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

    public function getObjectMetas($objectName)
    {
        if (! isset($this->objects[$objectName])) {
            throw new InvalidObjectException("Invalid object: $objectName");
        }

        // TODO: Cache

        return $this->createMetaContainer($this->objects[$objectName]["objectMeta"]);
    }

    public function getObjectPropertyMetas($objectName)
    {
        if (! isset($this->objects[$objectName])) {
            throw new InvalidObjectException("Invalid object: $objectName");
        }

        $propMetaContainerList = [];

        foreach ($this->objects[$objectName]["propMetaList"] as $propName => $propMetaList) {
            $propMetaContainerList[$propName] = $this->createMetaContainer($propMetaList);
        }

        // TODO: Cache

        return $propMetaContainerList;
    }

    public function getPropertyMetas($objectName, $propName)
    {
        if (! isset($this->objects[$objectName])) {
            throw new InvalidObjectException("Invalid object: $objectName");
        }

        if (! isset($this->objects[$objectName]["propMetaList"][$propName])) {
            throw new InvalidObjectException("Invalid object property: $objectName.$propName");
        }

        return $this->createMetaContainer($this->objects[$objectName]["propMetaList"][$propName]);
    }

    public function getPropertyMeta($objectName, $propName, $metaName)
    {
        if (! isset($this->objects[$objectName])) {
            throw new InvalidObjectException("Invalid object: $objectName");
        }

        if (! isset($this->objects[$objectName]["propMetaList"][$propName])) {
            throw new InvalidObjectException("Invalid object property: $objectName.$propName");
        }

        if (! $this->objects[$objectName]["propMetaList"][$propName][$metaName]) {
            throw new InvalidObjectException("Invalid object property meta: $objectName.$propName.$metaName");
        }

        return $this->composeMeta($this->objects[$objectName]["propMetaList"][$propName][$metaName]);
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
