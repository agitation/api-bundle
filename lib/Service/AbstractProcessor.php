<?php

/*
 * @package    agitation/api-bundle
 * @link       http://github.com/agitation/api-bundle
 * @author     Alexander Günsche
 * @license    http://opensource.org/licenses/MIT
 */

namespace Agit\ApiBundle\Service;

use Agit\ApiBundle\Annotation\Annotation;
use Agit\ApiBundle\Annotation\Depends;
use Agit\BaseBundle\Exception\InternalErrorException;
use Agit\BaseBundle\Service\ClassCollector;
use ReflectionClass;
use Symfony\Component\HttpKernel\Kernel;

abstract class AbstractProcessor
{
    private $entries = [];

    public function collect($subdir, $annotationClass, $cacheKey)
    {
        $classes = [];

        foreach ($this->kernel->getBundles() as $bundle) {
            $path = realpath($bundle->getPath() . "/$subdir");

            if (! $path) {
                continue;
            }

            foreach ($this->classCollector->collect($path) as $class) {
                $classRefl = new ReflectionClass($class);
                $desc = $this->annotationReader->getClassAnnotation($classRefl, $annotationClass);

                if ($desc) {
                    $this->processClass($classRefl, $desc);
                }
            }

            $this->cacheProvider->save($cacheKey, $this->getEntries());
        }
    }

    protected function addEntry($key, $entry)
    {
        $this->entries[$key] = $entry;
    }

    protected function getEntries()
    {
        return $this->entries;
    }

    protected function dissectMetaList($metas)
    {
        $newList = [];

        // While we could store the Meta objects as they are,
        // we’d have to unserialize hundreds of them on every API access.
        // Therefore we store class names and options separately.

        foreach ($metas as $name => $meta) {
            $newList[$name] = $this->dissectMeta($meta);
        }

        return $newList;
    }

    protected function dissectMeta(Annotation $meta)
    {
        return ["class" => get_class($meta), "options" => $meta->getOptions()];
    }

    protected function fixObjectName($namespace, $name)
    {
        return (preg_match("|^[a-z0-9]+\.v\d+/[a-z0-9\.]+(\[\])?$|i", $name))
            ? $name
            : "$namespace/$name";
    }

    protected function checkConstructor(ReflectionClass $classRefl, Depends $deps)
    {
        if (count($deps->get("value")) && ! $classRefl->getConstructor()) {
            throw new InternalErrorException(sprintf("Class %s has dependencies, but doesn’t have a constructor to inject them!", $classRefl->name));
        }
    }

    abstract protected function processClass(ReflectionClass $classRefl, Annotation $desc);
}
