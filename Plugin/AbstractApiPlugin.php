<?php
/**
 * @package    agitation/api
 * @link       http://github.com/agitation/AgitApiBundle
 * @author     Alex Günsche <http://www.agitsol.com/>
 * @copyright  2012-2015 AGITsol GmbH
 * @license    http://opensource.org/licenses/MIT
 */

namespace Agit\ApiBundle\Plugin;

use Agit\PluggableBundle\Strategy\ServiceAwarePluginInterface;
use Agit\PluggableBundle\Strategy\ServiceAwarePluginTrait;
use Agit\PluggableBundle\Strategy\Depends;
use Agit\PluggableBundle\Strategy\Cache\CachePluginInterface;
use Agit\PluggableBundle\Strategy\Cache\CacheEntry;

/**
 * @Depends({"annotation_reader", "agit.common.classcollector"})
 */
abstract class AbstractApiPlugin implements CachePluginInterface, ServiceAwarePluginInterface
{
    use ServiceAwarePluginTrait;

    private $entryList = [];

    final public function load()
    {
        $this->entryList = [];


        foreach ($this->getService("agit.common.classcollector")->collect($this->getSearchNamespace()) as $class)
        {
            $classRefl = new \ReflectionClass($class);

            if (!$classRefl->isSubclassOf($this->getBaseClass()))
                continue;

            $this->process($classRefl);
        }
    }

    final public function nextCacheEntry()
    {
        return array_pop($this->entryList);
    }

    // search namespace, to be provided by the plugin
    abstract protected function getSearchNamespace();

    // to be provided by endpoint/object/formatter child class
    abstract protected function getBaseClass();

    // to be provided by endpoint/object/formatter child class
    abstract protected function getType();

    // to be provided by endpoint/object/formatter child class
    abstract protected function process(\ReflectionClass $classRefl);

    protected function dissectMetaList($metaList)
    {
        $newList = [];

        // While we could store the Meta objects as they are,
        // we'd have to unserialize hundreds of them on every API access.
        // Therefore we store class names and options separately.

        foreach ($metaList as $name => $meta)
            $newList[$name] = ['class' => get_class($meta), 'options' => $meta->getOptions()];

        return $newList;
    }

    protected function fixObjectName($name)
    {
        $parts = [];

        if (strpos($name, '/'))
        {
            $nameParts = explode('/', $name);
            $parts['namespace'] = $nameParts[0];
            $parts['class'] = $nameParts[1];
        }
        else
        {
            $parts['namespace'] = $this->getApiNamespace();
            $parts['class'] = $name;
        }

        return implode('/', $parts);
    }

    protected function registerEntry($key, $value)
    {
        $cacheEntry = new CacheEntry();
        $cacheEntry->setId($key);
        $cacheEntry->setData($value);
        $this->entryList[] = $cacheEntry;
    }
}
