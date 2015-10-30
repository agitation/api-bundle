<?php
/**
 * @package    agitation/api
 * @link       http://github.com/agitation/AgitApiBundle
 * @author     Alex Günsche <http://www.agitsol.com/>
 * @copyright  2012-2015 AGITsol GmbH
 * @license    http://opensource.org/licenses/MIT
 */

namespace Agit\ApiBundle\Service;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Agit\CoreBundle\Pluggable\Strategy\Cache\CacheLoader;
use Agit\ApiBundle\Api\Endpoint\AbstractEndpoint;
use Agit\ApiBundle\Exception\IncompatibleFormatterException;

class FormatterService
{
    /**
     * @var service container instance.
     */
    protected $container;

    /**
     * @var CacheLoader instance.
     */
    protected $cacheLoader;

    private $formats;

    public function __construct(CacheLoader $cacheLoader, ContainerInterface $container)
    {
        $this->cacheLoader = $cacheLoader;
        $this->container = $container;
    }

    public function formatExists($format)
    {
        if (is_null($this->formats))
            $this->formats = $this->cacheLoader->loadPlugins();

        return isset($this->formats[$format]);
    }

    public function getFormatter($format, AbstractEndpoint $endpoint, Request $request)
    {
        if (!$this->formatExists($format))
            throw new IncompatibleFormatterException("Unknown data format.");

        $formatterClassName = $this->formats[$format];
        $formatter = new $formatterClassName($this->container, $endpoint, $request);

        return $formatter;
    }
}
