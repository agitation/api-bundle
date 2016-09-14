<?php

/*
 * @package    agitation/api-bundle
 * @link       http://github.com/agitation/api-bundle
 * @author     Alexander Günsche
 * @license    http://opensource.org/licenses/MIT
 */

namespace Agit\ApiBundle\Pluggable;

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
use ReflectionClass;

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

        $classRefl = new ReflectionClass($class);
        $namespace = $plugin->get("namespace");

        if (! $namespace) {
            return;
        } // TODO: throw exception after full migration of business code

        $objectName = "$namespace/" . StringHelper::getBareClassName($class);
        $objectMeta = [];
        $propMetaList = [];

        $plugin->set("objectName", $objectName);
        $objectMeta["Object"] = $plugin;

        foreach ($classRefl->getProperties() as $propertyRefl) {
            $annotations = $this->annotationReader->getPropertyAnnotations($propertyRefl);
            $propName = $propertyRefl->getName();
            $propMeta = [];

            foreach ($annotations as $annotation) {
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
            $objectMeta["Object"]->set("parentObjectName", strpos($superParent, "/") ? "" : "$namespace/" . $superParent);

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
            $annotation = $this->annotationReader->getClassAnnotation($refl, "Agit\ApiBundle\Annotation\Object\Object");

            if ($annotation && $annotation->get("super")) {
                $super = $annotation->get("super");
                break;
            }
        }

        return $super;
    }
}
