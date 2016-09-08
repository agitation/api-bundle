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
use Agit\BaseBundle\Exception\InternalErrorException;
use Agit\BaseBundle\Pluggable\Cache\CacheLoaderFactory;
use Agit\BaseBundle\Pluggable\ServiceInjectorTrait;
use Agit\ApiBundle\Exception\InvalidEndpointException;
use Agit\ApiBundle\Exception\UnauthorizedException;
use Agit\UserBundle\Service\UserService;
use Agit\IntlBundle\Tool\Translate;
use Agit\ApiBundle\Common\AbstractController;

class EndpointService
{
    use ServiceInjectorTrait;
    use MetaAwareTrait;

    protected $userService;

    protected $cacheLoader;

    protected $container;

    private $endpoints;

    public function __construct(CacheLoaderFactory $cacheLoaderFactory, UserService $userService = null, ContainerInterface $container)
    {
        $this->cacheLoader = $cacheLoaderFactory->create("agit.api.endpoint");
        $this->userService = $userService;
        $this->container = $container;
    }

    public function createEndpoint($name, Request $request = null)
    {
        $this->loadEndpoints();

        if (!isset($this->endpoints[$name]))
            throw new InvalidEndpointException("Invalid endpoint: $name");

        $metaContainer = $this->createMetaContainer($this->endpoints[$name]["meta"]);

        $class = $this->endpoints[$name]["class"];
        $endpoint = new $class(
            $name,
            $metaContainer,
            $this->container->get("agit.api.request"),
            $this->container->get("agit.api.response"),
            $this->container->get("agit.logger"),
            $request
        );

        $this->checkAuthorisation($endpoint);

        $this->injectServices($endpoint, $metaContainer->get("Endpoint")->get("depends"));

        return $endpoint;
    }

    public function getEndpointNames()
    {
        $this->loadEndpoints();
        return array_keys($this->endpoints);
    }

    public function getController($name)
    {
        $this->loadEndpoints();

        if (!isset($this->endpoints[$name]))
            throw new InternalErrorException("This endpoint does not exist.");

        return $this->endpoints[$name]["class"];
    }

    public function getEndpointMetaContainer($name)
    {
        $this->loadEndpoints();

        if (!isset($this->endpoints[$name]))
            throw new InternalErrorException("This endpoint does not exist.");

        return $this->createMetaContainer($this->endpoints[$name]["meta"]);
    }

    protected function loadEndpoints()
    {
        if (is_null($this->endpoints))
            $this->endpoints = $this->cacheLoader->load();
    }

    private function checkAuthorisation(AbstractController $endpoint)
    {
        $reqCapibilty = $endpoint->getMeta("Security")->get("capability");

        if (is_null($reqCapibilty))
            throw new InternalErrorException("The endpoint call must specify the required capabilities.");

        if ($reqCapibilty)
        {
            if (!$this->userService)
                throw new InternalErrorException("The `agitation/user` bundle must be loaded to support capability-aware endpoints.");

            $user = $this->userService->getCurrentUser();

            if (!$user)
                throw new UnauthorizedException(Translate::t("You must be logged in to perform this operation."));

            if (!$user->hasCapability($reqCapibilty))
                throw new UnauthorizedException(Translate::t("You do not have sufficient permissions to perform this operation."));
        }
    }
}
