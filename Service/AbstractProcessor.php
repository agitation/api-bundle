<?php

/*
 * @package    agitation/api-bundle
 * @link       http://github.com/agitation/api-bundle
 * @author     Alexander Günsche
 * @license    http://opensource.org/licenses/MIT
 */

namespace Agit\ApiBundle\Service;

use Agit\ApiBundle\Annotation\Annotation;
use Agit\BaseBundle\Service\ClassCollector;
use Doctrine\Common\Annotations\Reader;
use Doctrine\Common\Cache\Cache;
use ReflectionClass;
use Symfony\Component\HttpKernel\Kernel;

abstract class AbstractProcessor
{
    private $kernel;

    private $classCollector;

    private $cacheProvider;

    protected $annotationReader;

    private $entryList = [];

    public function __construct(Kernel $kernel, ClassCollector $classCollector, Reader $annotationReader, Cache $cacheProvider)
    {
        $this->kernel = $kernel;
        $this->classCollector = $classCollector;
        $this->cacheProvider = $cacheProvider;
        $this->annotationReader = $annotationReader;
    }

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
        $this->entryList[$key] = $entry;
    }

    protected function getEntries()
    {
        return $this->entryList;
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

    abstract protected function processClass(ReflectionClass $classRefl, Annotation $desc);
}
