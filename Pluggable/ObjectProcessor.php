<?php

/*
 * @package    agitation/api-bundle
 * @link       http://github.com/agitation/api-bundle
 * @author     Alexander Günsche
 * @license    http://opensource.org/licenses/MIT
 */

/**
 * @link       http://github.com/agitation/AgitApiBundle
 *
 * @author     Alex Günsche <http://www.agitsol.com/>
 * @copyright  2012-2015 AGITsol GmbH
 * @license    http://opensource.org/licenses/MIT
 */

namespace Agit\ApiBundle\Pluggable;

use Agit\ApiBundle\Annotation\Object\AbstractObjectMeta;
use Agit\ApiBundle\Annotation\Object\Object;
use Agit\ApiBundle\Annotation\Property\AbstractPropertyMeta;
use Agit\ApiBundle\Annotation\Property\AbstractType;
use Agit\ApiBundle\Annotation\Property\Name;
use Agit\ApiBundle\Annotation\Property\ObjectType;
use Agit\ApiBundle\Annotation\Property\StringType;
use Agit\BaseBundle\Exception\InternalErrorException;
use Agit\BaseBundle\Pluggable\PluggableServiceInterface;
use Agit\BaseBundle\Pluggable\PluginInterface;
use Agit\BaseBundle\Pluggable\ProcessorInterface;
use Agit\BaseBundle\Tool\StringHelper;
use Doctrine\Common\Annotations\Reader;
use Doctrine\Common\Cache\CacheProvider;

class ObjectProcessor extends AbstractApiProcessor implements ProcessorInterface
{
    private $cacheProvider;

    private $annotationReader;

    private $entryList = [];

    public function __construct(Reader $annotationReader, CacheProvider $cacheProvider, PluggableServiceInterface $pluggableService)
    {
        if (! ($pluggableService instanceof ObjectService)) {
            throw new InternalErrorException("Pluggable service must be an instance of ObjectService.");
        }

        $this->cacheProvider = $cacheProvider;
        $this->annotationReader = $annotationReader;
    }

    public function addPlugin($class, PluginInterface $plugin)
    {
        if ($plugin->get("objectName") !== null) {
            throw new InternalErrorException("Error in Object annotation on $class: You must not set the `objectName` parameter, it will be set automatically.");
        }

        $objectMeta = [];
        $propMetaList = [];
        $classRefl = new \ReflectionClass($class);
        $objectName = $this->translateName($classRefl);
        $namespace = strstr($objectName, "/", true);

        $objAnnotations = $this->annotationReader->getClassAnnotations($classRefl);

        foreach ($objAnnotations as $annotation) {
            if (! ($annotation instanceof AbstractObjectMeta)) {
                continue;
            }

            $objMetaName = StringHelper::getBareClassName($annotation);
            $objectMeta[$objMetaName] = $annotation;
        }

        $plugin->set("objectName", $objectName);
        $objectMeta["Object"] = $plugin;

        foreach ($classRefl->getProperties() as $propertyRefl) {
            $annotationList = $this->annotationReader->getPropertyAnnotations($propertyRefl);
            $propName = $propertyRefl->getName();
            $propMeta = [];

            foreach ($annotationList as $annotation) {
                if (! ($annotation instanceof AbstractPropertyMeta)) {
                    continue;
                }

                $propMetaClass = StringHelper::getBareClassName($annotation);
                $propMetaName = ($annotation instanceof AbstractType) ? "Type" : $propMetaClass;
                $propMeta[$propMetaName] = $annotation;
            }

            if (! isset($propMeta["Type"])) {
                continue;
            }

            if ($propMeta["Type"] instanceof ObjectType) {
                $targetClass = $propMeta["Type"]->get("class");

                if (is_null($targetClass)) {
                    throw new InternalErrorException("Error in $objectName, property $propName: The target class must be specified.");
                }

                $propMeta["Type"]->set("class", $this->fixObjectName($namespace, $targetClass));
            }

            if (! isset($propMeta["Name"]) || ! $propMeta["Name"]->get("value")) {
                $propMeta["Name"] = new Name(["value" => $propName]);
            }

            $propMetaList[$propName] = $this->dissectMetaList($propMeta);
        }

        // check scalar "objects"
        if ($objectMeta["Object"]->get("scalar") && (count($propMetaList) !== 1 || ! isset($propMetaList["_"]))) {
            throw new InternalErrorException("Scalar objects must contain only a `_` property.");
        }

        // handle super-class children
        if ($superParent = $this->getSuperParent($classRefl)) {
            $objectMeta["Object"]->set("parentObjectName", $superParent);

            $propMetaList["_class"] = $this->dissectMetaList([
                "Type" => new StringType(["meta" => "class"])
            ]);
        }

        $this->addEntry($objectName, [
            "class"        => $classRefl->getName(),
            "objectMeta"   => $this->dissectMetaList($objectMeta),
            "propMetaList" => $propMetaList
        ]);
    }

    public function process()
    {
        $this->cacheProvider->save("agit.api.object", $this->getEntries());
    }

    private function getSuperParent($refl)
    {
        $super = null;

        while ($refl = $refl->getParentClass()) {
            $annotations = $this->annotationReader->getClassAnnotations($refl);

            foreach ($annotations as $annotation) {
                if ($annotation instanceof Object && $annotation->get("super")) {
                    $super = $this->translateName($refl);
                }
            }
        }

        return $super;
    }
}
