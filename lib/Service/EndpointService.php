<?php
declare(strict_types=1);

/*
 * @package    agitation/api-bundle
 * @link       http://github.com/agitation/api-bundle
 * @author     Alexander Günsche
 * @license    http://opensource.org/licenses/MIT
 */

namespace Agit\ApiBundle\Service;

use Agit\ApiBundle\Api\Controller\AbstractEntityController;

use Agit\IntlBundle\Tool\Translate;
use Agit\UserBundle\Service\UserService;
use Doctrine\Common\Cache\Cache;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

class EndpointService
{
    use MetaAwareTrait;

    /**
     * @var ResponseService
     */
    protected $responseService;

    /**
     * @var PersistenceService
     */
    protected $persistenceService;

    /**
     * @var EntityManagerInterface
     */
    protected $entityManager;

    /**
     * @var Factory
     */
    protected $factory;

    /**
     * @var UserService
     */
    protected $userService;

    private $endpoints = [];

    private $endpointMetas = [];

    public function __construct(
        Cache $cache,
        ResponseService $responseService,
        PersistenceService $persistenceService,
        EntityManagerInterface $entityManager,
        Factory $factory,
        UserService $userService
    ) {
        $this->endpoints = $cache->fetch('agit.api.endpoint') ?: [];
        $this->responseService = $responseService;
        $this->persistenceService = $persistenceService;
        $this->entityManager = $entityManager;
        $this->factory = $factory;
        $this->userService = $userService;
    }

    public function createEndpointController($name, Request $request = null)
    {
        if (! isset($this->endpoints[$name]))
        {
            throw new BadRequestHttpException("Invalid endpoint: $name");
        }

        $metaContainer = $this->getEndpointMetaContainer($name);
        $this->checkAuthorisation($metaContainer);

        $deps = $this->composeMeta($this->endpoints[$name]['deps']);
        $controller = $this->factory->create($this->endpoints[$name]['class'], $deps);

        $controller->init($name, $metaContainer, $this->responseService);

        if ($controller instanceof AbstractEntityController)
        {
            $controller->initExtra($this->entityManager, $this->persistenceService);
        }

        return $controller;
    }

    public function getEndpointNames()
    {
        return array_keys($this->endpoints);
    }

    public function getControllerClass($endpointName)
    {
        if (! isset($this->endpoints[$endpointName]))
        {
            throw new NotFoundHttpException('This endpoint does not exist.');
        }

        return $this->endpoints[$endpointName]['class'];
    }

    public function getEndpointMetaContainer($name)
    {
        if (! isset($this->endpoints[$name]))
        {
            throw new NotFoundHttpException('This endpoint does not exist.');
        }

        if (! isset($this->endpointMetas[$name]))
        {
            $this->endpointMetas[$name] = $this->createMetaContainer($this->endpoints[$name]['meta']);
        }

        return $this->endpointMetas[$name];
    }

    private function checkAuthorisation($metaContainer)
    {
        $reqCapibilty = $metaContainer->get('Security')->get('capability');

        if ($reqCapibilty === null)
        {
            throw new Exception('The endpoint call must specify the required capabilities.');
        }

        if ($reqCapibilty)
        {
            if (! $this->userService->currentUserCan($reqCapibilty))
            {
                throw new UnauthorizedHttpException(Translate::t('You do not have sufficient permissions to perform this operation.'));
            }
        }
    }
}
