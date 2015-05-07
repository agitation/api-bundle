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
use Agit\CoreBundle\Pluggable\Strategy\Cache\CacheLoader;
use Agit\ApiBundle\Exception\InvalidEndpointException;

class EndpointService extends AbstractApiService
{
    /**
     * @var service container instance.
     */
    protected $Container;

    /**
     * @var CacheLoader instance.
     */
    protected $CacheLoader;

    private $endpoints;

    public function __construct(CacheLoader $CacheLoader, ContainerInterface $Container)
    {
        $this->CacheLoader = $CacheLoader;
        $this->Container = $Container;
    }

    public function createEndpoint($endpointCall, Request $Request = null)
    {
        if (is_null($this->endpoints))
            $this->endpoints = $this->CacheLoader->loadPlugins();

        if (!isset($this->endpoints[$endpointCall]))
            throw new InvalidEndpointException("Invalid endpoint: $endpointCall");


        $MetaContainer = $this->createMetaContainer($this->endpoints[$endpointCall]['meta']);

        $className = $this->endpoints[$endpointCall]['class'];
        $Endpoint = new $className($this->Container, $MetaContainer, $Request);

        return $Endpoint;
    }

    public function getEndpointNames()
    {
        if (is_null($this->endpoints))
            $this->endpoints = $this->CacheLoader->loadPlugins();


        return $this->endpoints;
    }
}
