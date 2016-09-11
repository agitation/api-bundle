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

use Agit\ApiBundle\Annotation\Formatter\Formatter;
use Agit\BaseBundle\Exception\InternalErrorException;
use Agit\BaseBundle\Pluggable\PluggableServiceInterface;
use Agit\BaseBundle\Pluggable\PluginInterface;
use Agit\BaseBundle\Pluggable\ProcessorInterface;
use Doctrine\Common\Annotations\Reader;
use Doctrine\Common\Cache\CacheProvider;

class FormatterProcessor extends AbstractApiProcessor implements ProcessorInterface
{
    private $cacheProvider;

    private $annotationReader;

    private $entryList = [];

    public function __construct(Reader $annotationReader, CacheProvider $cacheProvider, PluggableServiceInterface $pluggableService)
    {
        if (! ($pluggableService instanceof FormatterService)) {
            throw new InternalErrorException("Pluggable service must be an instance of FormatterService.");
        }

        $this->cacheProvider = $cacheProvider;
        $this->annotationReader = $annotationReader;
    }

    public function addPlugin($class, PluginInterface $plugin)
    {
        if ($plugin instanceof Formatter) {
            $this->addEntry(
                $plugin->get("format"),
                ["class" => $class, "meta" => $this->dissectMeta($plugin)]
            );
        }
    }

    public function process()
    {
        $this->cacheProvider->save("agit.api.formatter", $this->getEntries());
    }
}
