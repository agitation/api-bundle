<?php
/**
 * @package    agitation/api
 * @link       http://github.com/agitation/AgitApiBundle
 * @author     Alex Günsche <http://www.agitsol.com/>
 * @copyright  2012-2015 AGITsol GmbH
 * @license    http://opensource.org/licenses/MIT
 */

namespace Agit\ApiBundle\Service;

use Agit\PluggableBundle\Strategy\Cache\CacheLoaderFactory;
use Agit\ValidationBundle\Service\ValidationService;
use Agit\ApiBundle\Exception\InvalidObjectException;
use Agit\ApiBundle\Annotation\Property\AbstractType;
use Agit\CommonBundle\Exception\InternalErrorException;

class ObjectMetaService
{
    use MetaAwareTrait;

    private $cacheLoaderFactory;

    private $validationService;

    public function __construct(CacheLoaderFactory $cacheLoaderFactory, ValidationService $validationService)
    {
        $this->objects = $cacheLoaderFactory->create("agit.api.object")->load();
        AbstractType::setValidationService($validationService);
    }

    public function createObject($objectName)
    {
        $objectClass = $this->getObjectClass($objectName);
        $objectMetas = $this->getObjectMetas($objectName);
        $objectPropertyMetas = $this->getObjectPropertyMetas($objectName);

        return new $objectClass($objectMetas, $objectPropertyMetas);
    }

    public function getObjectNames()
    {
        return array_keys($this->objects);
    }

    public function getObjectClass($objectName)
    {
        if (!isset($this->objects[$objectName]))
            throw new InvalidObjectException("Invalid object: $objectName");

        return $this->objects[$objectName]["class"];
    }

    public function getObjectMetas($objectName)
    {
        if (!isset($this->objects[$objectName]))
            throw new InvalidObjectException("Invalid object: $objectName");

        return $this->createMetaContainer($this->objects[$objectName]["objectMeta"]);
    }

    public function getObjectPropertyMetas($objectName)
    {
        if (!isset($this->objects[$objectName]))
            throw new InvalidObjectException("Invalid object: $objectName");

        $propMetaContainerList = [];

        foreach ($this->objects[$objectName]["propMetaList"] as $propName => $propMetaList)
            $propMetaContainerList[$propName] = $this->createMetaContainer($propMetaList);

        return $propMetaContainerList;
    }

    public function getPropertyMetas($objectName, $propName)
    {
        if (!isset($this->objects[$objectName]))
            throw new InvalidObjectException("Invalid object: $objectName");

        if (!isset($this->objects[$objectName]["propMetaList"][$propName]))
            throw new InvalidObjectException("Invalid object property: $objectName.$propName");

        return $this->createMetaContainer($this->objects[$objectName]["propMetaList"][$propName]);
    }

    public function getPropertyMeta($objectName, $propName, $metaName)
    {
        if (!isset($this->objects[$objectName]))
            throw new InvalidObjectException("Invalid object: $objectName");

        if (!isset($this->objects[$objectName]["propMetaList"][$propName]))
            throw new InvalidObjectException("Invalid object property: $objectName.$propName");

        if (!$this->objects[$objectName]["propMetaList"][$propName][$metaName])
            throw new InvalidObjectException("Invalid object property meta: $objectName.$propName.$metaName");

        return $this->composeMeta($this->objects[$objectName]["propMetaList"][$propName][$metaName]);
    }

    public function getObjectNameFromClass($class)
    {
        if (is_null($this->classes))
        {
            $this->classes = [];

            foreach ($this->objects as $objectName => $data)
                $this->classes[$data["class"]] = $objectName;
        }

        if (!isset($this->classes[$class]))
            throw new InternalErrorException("Class `$class` has not been registered.");

        return $this->classes[$class];
    }
}
