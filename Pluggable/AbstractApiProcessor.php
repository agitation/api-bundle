<?php
/**
 * @package    agitation/api
 * @link       http://github.com/agitation/AgitApiBundle
 * @author     Alex Günsche <http://www.agitsol.com/>
 * @copyright  2012-2015 AGITsol GmbH
 * @license    http://opensource.org/licenses/MIT
 */

namespace Agit\ApiBundle\Pluggable;

use Agit\CommonBundle\Annotation\SerializableAnnotationInterface;
use Agit\CommonBundle\Exception\InternalErrorException;
use Agit\PluggableBundle\Strategy\ProcessorInterface;

abstract class AbstractApiProcessor implements ProcessorInterface
{
    private $entryList = [];

    protected function addEntry($key, $entry)
    {
        $this->entryList[$key] = $entry;
    }

    protected function getEntries()
    {
        return $this->entryList;
    }

    protected function dissectMetaList($metaList)
    {
        $newList = [];

        // While we could store the Meta objects as they are,
        // we'd have to unserialize hundreds of them on every API access.
        // Therefore we store class names and options separately.

        foreach ($metaList as $name => $meta)
            $newList[$name] = $this->dissectMeta($meta);

        return $newList;
    }

    protected function dissectMeta(SerializableAnnotationInterface $meta)
    {
        return ['class' => get_class($meta), 'options' => $meta->getOptions()];
    }

    protected function translateName(\ReflectionClass $classRefl)
    {
        $nsParts = array_reverse(explode('\\', $classRefl->getNamespaceName()));

        if (!preg_match('|^[A-Z][A-Za-z]+V\d+$|', $nsParts[1]))
            throw new InternalErrorException(sprintf("Error in %s: API object and endpoint class namespaces must follow the pattern \Foo\Bar\NamespaceVxx\Type\Class.", $classRefl->getName()));

        $namespace = strtolower(preg_replace('|(V\d+)$|', '.\1', $nsParts[1]));
        $name = $classRefl->getShortName();

        return "$namespace/$name";
    }

    protected function fixObjectName($namespace, $name)
    {
        return (preg_match("|^[a-z0-9]+\.v\d+/[a-z0-9\.]+$|i", $name))
            ? $name
            : "$namespace/$name";
    }

    protected function registerEntry($key, $value)
    {
        $cacheEntry = new CacheEntry();
        $cacheEntry->setId($key);
        $cacheEntry->setData($value);
        $this->entryList[] = $cacheEntry;
    }
}
