<?php

/*
 * @package    agitation/api-bundle
 * @link       http://github.com/agitation/api-bundle
 * @author     Alexander Günsche
 * @license    http://opensource.org/licenses/MIT
 */

namespace Agit\ApiBundle\Service;

use Agit\ApiBundle\Api\Controller\AbstractEntityController;
use Agit\ApiBundle\Exception\InvalidEndpointException;
use Agit\ApiBundle\Exception\UnauthorizedException;
use Agit\BaseBundle\Exception\InternalErrorException;
use Agit\IntlBundle\Tool\Translate;
use Agit\LoggingBundle\Service\Logger;
use Agit\UserBundle\Service\UserService;
use Doctrine\Common\Cache\Cache;
use Doctrine\ORM\EntityManager;
use Symfony\Component\HttpFoundation\Request;

class EndpointService
{
    use MetaAwareTrait;

    private $endpoints = [];

    private $endpointMetas = [];

    protected $responseService;

    protected $persistenceService;

    protected $logger;

    protected $entityManager;

    protected $factory;

    protected $userService;

    public function __construct(
        Cache $cache,
        ResponseService $responseService,
        PersistenceService $persistenceService,
        EntityManager $entityManager,
        Factory $factory,
        Logger $logger = null,
        UserService $userService = null
    ) {
        $this->endpoints = $cache->fetch("agit.api.endpoint") ?: [];
        $this->responseService = $responseService;
        $this->persistenceService = $persistenceService;
        $this->entityManager = $entityManager;
        $this->factory = $factory;
        $this->userService = $userService;
        $this->logger = $logger;
    }

    public function createEndpointController($name, Request $request = null)
    {
        if (! isset($this->endpoints[$name])) {
            throw new InvalidEndpointException("Invalid endpoint: $name");
        }

        $metaContainer = $this->getEndpointMetaContainer($name);
        $this->checkAuthorisation($metaContainer);

        $deps = $this->composeMeta($this->endpoints[$name]["deps"]);
        $controller = $this->factory->create($this->endpoints[$name]["class"], $deps);

        $controller->init($name, $metaContainer, $this->responseService);

        if ($controller instanceof AbstractEntityController) {
            $controller->initExtra($this->persistenceService, $this->entityManager, $this->logger);
        }

        return $controller;
    }

    public function getEndpointNames()
    {
        return array_keys($this->endpoints);
    }

    public function getControllerClass($endpointName)
    {
        if (! isset($this->endpoints[$endpointName])) {
            throw new InternalErrorException("This endpoint does not exist.");
        }

        return $this->endpoints[$endpointName]["class"];
    }

    public function getEndpointMetaContainer($name)
    {
        if (! isset($this->endpoints[$name])) {
            throw new InternalErrorException("This endpoint does not exist.");
        }

        if (! isset($this->endpointMetas[$name])) {
            $this->endpointMetas[$name] = $this->createMetaContainer($this->endpoints[$name]["meta"]);
        }

        return $this->endpointMetas[$name];
    }

    private function checkAuthorisation($metaContainer)
    {
        $reqCapibilty = $metaContainer->get("Security")->get("capability");

        if (is_null($reqCapibilty)) {
            throw new InternalErrorException("The endpoint call must specify the required capabilities.");
        }

        if ($reqCapibilty) {
            if (! $this->userService) {
                throw new InternalErrorException("The `agitation/user` bundle must be loaded to support capability-aware endpoints.");
            }

            $user = $this->userService->getCurrentUser();

            if (! $user) {
                throw new UnauthorizedException(Translate::t("You must be logged in to perform this operation."));
            }

            if (! $user->hasCapability($reqCapibilty)) {
                throw new UnauthorizedException(Translate::t("You do not have sufficient permissions to perform this operation."));
            }
        }
    }
}
