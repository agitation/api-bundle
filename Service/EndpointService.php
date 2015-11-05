<?php
/**
 * @package    agitation/api
 * @link       http://github.com/agitation/AgitApiBundle
 * @author     Alex Günsche <http://www.agitsol.com/>
 * @copyright  2012-2015 AGITsol GmbH
 * @license    http://opensource.org/licenses/MIT
 */

namespace Agit\ApiBundle\Service;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Agit\CoreBundle\Exception\InternalErrorException;
use Agit\PluggableBundle\Strategy\Cache\CacheLoaderFactory;
use Agit\ApiBundle\Exception\InvalidEndpointException;

class EndpointService extends AbstractApiService
{
    /**
     * @var service container instance.
     */
    protected $container;

    /**
     * @var CacheLoader instance.
     */
    protected $cacheLoader;

    private $endpoints;

    public function __construct(CacheLoaderFactory $CacheLoaderFactory, ContainerInterface $container)
    {
        $this->cacheLoader = $CacheLoaderFactory->create("agit.api.endpoint");
        $this->container = $container;
    }

    public function createEndpoint($endpointCall, Request $request = null)
    {
        if (is_null($this->endpoints))
            $this->endpoints = $this->cacheLoader->loadPlugins();

        if (!isset($this->endpoints[$endpointCall]))
            throw new InvalidEndpointException("Invalid endpoint: $endpointCall");


        $metaContainer = $this->createMetaContainer($this->endpoints[$endpointCall]['meta']);

        $className = $this->endpoints[$endpointCall]['class'];
        $endpoint = new $className($this->container, $metaContainer, $request);

        return $endpoint;
    }

    public function getEndpointNames()
    {
        if (is_null($this->endpoints))
            $this->endpoints = $this->cacheLoader->loadPlugins();

        return $this->endpoints;
    }
}
