<?php

/*
 * @package    agitation/api-bundle
 * @link       http://github.com/agitation/api-bundle
 * @author     Alexander Günsche
 * @license    http://opensource.org/licenses/MIT
 */

namespace Agit\ApiBundle\Pluggable;

use Agit\ApiBundle\Annotation\Annotation;
use Agit\BaseBundle\Pluggable\ProcessorInterface;

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
        // we’d have to unserialize hundreds of them on every API access.
        // Therefore we store class names and options separately.

        foreach ($metaList as $name => $meta) {
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

    protected function registerEntry($key, $value)
    {
        $cacheEntry = new CacheEntry();
        $cacheEntry->setId($key);
        $cacheEntry->setData($value);
        $this->entryList[] = $cacheEntry;
    }
}
