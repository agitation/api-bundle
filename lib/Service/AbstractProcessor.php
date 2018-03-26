<?php
declare(strict_types=1);

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
use Doctrine\Common\Cache\Cache;
use ReflectionClass;
use Symfony\Component\HttpKernel\CacheWarmer\CacheWarmerInterface;

abstract class AbstractProcessor implements CacheWarmerInterface, SimpleTypesInterface
{
    const API_SUBDIR = 'Api';

    private $entries = [];

    public function collect($annotationClass, $cacheKey)
    {
        $classes = [];

        foreach ($this->kernel->getBundles() as $bundle)
        {
            $path = realpath($bundle->getPath() . '/' . static::API_SUBDIR);

            if (! $path)
            {
                continue;
            }

            foreach ($this->classCollector->collect($path) as $class)
            {
                $classRefl = new ReflectionClass($class);

                $annotations = $this->getAllClassAnnotations($classRefl);

                foreach ($annotations as $annoClass => $anno)
                {
                    if ($anno instanceof $annotationClass)
                    {
                        unset($annotations[$annoClass]);
                        $this->processClass($classRefl, $anno, $annotations);

                        break;
                    }
                }
            }

            $this->cacheProvider->save($cacheKey, $this->getEntries());
        }
    }

    /**
     * Warms up the cache, required by CacheWarmerInterface.
     * @param mixed $cacheDir
     */
    public function warmUp($cacheDir)
    {
        $this->process();
    }

    /**
     * required by CacheWarmerInterface.
     */
    public function isOptional()
    {
        return true;
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

        foreach ($metas as $name => $meta)
        {
            $newList[$name] = $this->dissectMeta($meta);
        }

        return $newList;
    }

    protected function dissectMeta(Annotation $meta)
    {
        return ['class' => get_class($meta), 'options' => $meta->getOptions()];
    }

    protected function fixObjectName($namespace, $name)
    {
        $testName = (substr($name, -2) === '[]') ? substr($name, 0, -2) : $name;

        return (preg_match("|^[a-z0-9]+\.v\d+/[a-z0-9\.]+$|i", $testName) || in_array($testName, self::SIMPLE_TYPES))
            ? $name
            : "$namespace/$name";
    }

    protected function checkConstructor(ReflectionClass $classRefl, Depends $deps)
    {
        if (count($deps->get('value')) && ! $classRefl->getConstructor())
        {
            throw new InternalErrorException(sprintf('Class %s has dependencies, but doesn’t have a constructor to inject them!', $classRefl->name));
        }
    }

    /**
     * gets annotations from a class AND ITS ANCESTORS.
     */
    protected function getAllClassAnnotations(ReflectionClass $classRefl)
    {
        $annotations = [];

        foreach ($this->annotationReader->getClassAnnotations($classRefl) as $k => $anno)
        {
            $annotations[get_class($anno)] = $anno;
        }

        if ($parent = $classRefl->getParentClass())
        {
            $annotations += $this->getAllClassAnnotations($parent);
        }

        return $annotations;
    }

    abstract protected function processClass(ReflectionClass $classRefl, Annotation $desc, array $classAnnotations);
}
