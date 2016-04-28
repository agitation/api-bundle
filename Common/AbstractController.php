<?php
/**
 * @package    agitation/api
 * @link       http://github.com/agitation/AgitApiBundle
 * @author     Alex Günsche <http://www.agitsol.com/>
 * @copyright  2012-2015 AGITsol GmbH
 * @license    http://opensource.org/licenses/MIT
 */

namespace Agit\ApiBundle\Common;

use Symfony\Component\HttpFoundation\Request;
use Agit\CommonBundle\Exception\AgitException;
use Agit\CommonBundle\Exception\InternalErrorException;
use Agit\PluggableBundle\Strategy\ServiceAwarePluginInterface;
use Agit\PluggableBundle\Strategy\ServiceAwarePluginTrait;
use Agit\IntlBundle\Translate;
use Agit\ApiBundle\Annotation\MetaContainer;
use Agit\ApiBundle\Service\RequestService;
use Agit\ApiBundle\Service\ResponseService;
use Agit\ApiBundle\Exception\ObjectNotFoundException;
use Agit\ApiBundle\Exception\BadRequestException;

/**
 * Generic Endpoint handler. To be inherited by an API version specific
 * handler, which again is inherited by the actual Endpoint.
 */
abstract class AbstractController implements ServiceAwarePluginInterface
{
    // including the service-aware features here, because almost all endpoints
    // have service dependencies.
    use ServiceAwarePluginTrait;

    /**
     * @var full endpoint name, e.g. `foobar.v1/foo.bar`.
     */
    protected $name;

    /**
     * @var API namespace.
     */
    protected $apiNamespace;

    /**
     * @var endpoint method to execute.
     */
    protected $endpoint;

    /**
     * @var MetaContainer instance.
     */
    protected $meta;

    /**
     * @var Symfony's request object, only used for security checks
     */
    private $httpRequest;

    /**
     * @var processed request data.
     */
    private $request;

    /**
     * @var indicates that the request has been processed.
     */
    private $haveProcessedRequest = false;

    /**
     * @var the generated response payload
     */
    private $response;

    // TODO: Instead of injecting the $requestService, pass the RequestObject to executeCall()

    public function __construct($name, MetaContainer $meta, RequestService $requestService, ResponseService $responseService, Request $httpRequest = null)
    {
        $this->endpoint = substr(strrchr($name, '.'), 1);
        $this->apiNamespace = strstr($name, '/', true);
        $this->meta = $meta;
        $this->requestService = $requestService;
        $this->responseService = $responseService;
        $this->httpRequest = $httpRequest;
    }

    public function getMeta($name)
    {
        return $this->meta->get($name);
    }

    public function getResponse()
    {
        return $this->response;
    }

    // this is a separate method (and not in the constructor) because there
    // may be an internal "redirect" where setup and checks are not required.
    public function setupEnvironment()
    {
        if (!$this->httpRequest)
            throw new InternalErrorException("The request object could not be created, as the actual request has not been passed to the endpoint.");

        $request = json_decode($this->httpRequest->get('request'));

        // allow literal strings without quotes
        if (is_null($request) && strlen($this->httpRequest->get('request')))
            $request = $this->httpRequest->get('request');

        $this->request = $this->requestService
            ->createRequestObject($this->getMeta('Endpoint')->get('request'), $request);

        $this->haveProcessedRequest = true;
    }

    public function executeCall()
    {
        if (!$this->haveProcessedRequest)
            throw new InternalErrorException("The request object must be processed before executing the call.");

        $this->response = call_user_func([$this, $this->endpoint], $this->request);
    }

    protected function createObject($name, $data = null)
    {
        if (strpos($name, '/') === false)
            $name = "{$this->apiNamespace}/$name";

        return $this->responseService->createResponseObject($name, $data);
    }
}