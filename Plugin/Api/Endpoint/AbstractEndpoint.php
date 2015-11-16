<?php
/**
 * @package    agitation/api
 * @link       http://github.com/agitation/AgitApiBundle
 * @author     Alex Günsche <http://www.agitsol.com/>
 * @copyright  2012-2015 AGITsol GmbH
 * @license    http://opensource.org/licenses/MIT
 */

namespace Agit\ApiBundle\Plugin\Api\Endpoint;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Agit\CommonBundle\Exception\AgitException;
use Agit\CommonBundle\Exception\InternalErrorException;
use Agit\IntlBundle\Translate;
use Agit\ApiBundle\Annotation\MetaContainer;
use Agit\ApiBundle\Plugin\Api\Object\AbstractObject;
use Agit\ApiBundle\Exception\ObjectNotFoundException;
use Agit\ApiBundle\Exception\BadRequestException;
use Agit\ApiBundle\Exception\UnauthorizedException;

/**
 * Generic Endpoint handler. To be inherited by an API version specific
 * handler, which again is inherited by the actual Endpoint.
 */
abstract class AbstractEndpoint
{
    /**
     * @var service container instance.
     */
    protected $container;

    /**
     * @var MetaContainer instance.
     */
    protected $meta;

    /**
     * @var indicates whether the call was successful or not.
     */
    private $success = null;

    /**
     * @var list of messages, usually errors or warnings
     */
    private $messageList = [];

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
     * @var the generated response data
     */
    private $response;

    public function __construct(ContainerInterface $container, MetaContainer $meta, Request $httpRequest = null)
    {
        $this->container = $container;
        $this->meta = $meta;
        $this->httpRequest = $httpRequest;
    }

    public function getMeta($name)
    {
        return $this->meta->get($name);
    }

    protected function setSuccess($success)
    {
        $this->success = $success;
    }

    public function getSuccess()
    {
        return $this->success;
    }

    protected function addMessage($type, $text, $code = null)
    {
        $this->messageList[] = $this->createObject(
            'common.v1/Message',
            (object)['type' => $type, 'code' => $code, 'text' => $text]
        );
    }

    public function getMessages()
    {
        return $this->messageList;
    }

    public function getResponse()
    {
        return $this->response;
    }

    // this is a separate method (and not in the constructor) because there
    // may be an internal "redirect" where setup and checks are not required.
    public function setupEnvironment()
    {
        try
        {
            $this->checkAuthorisation();

            if (!$this->getMeta('Security')->get('allowCrossOrigin'))
                $this->getService('agit.api.csrf')->checkToken($this->getCsrfToken());

            if (!$this->httpRequest)
                throw new InternalErrorException("The request object could not be created, as the actual request has not been passed to the endpoint.");

            $request = json_decode($this->httpRequest->get('request'));

            // allow literal strings without quotes
            if (is_null($request) && strlen($this->httpRequest->get('request')))
                $request = $this->httpRequest->get('request');

            $this->request = $this->getService('agit.api.object')
                ->rawRequestToApiObject($request, $this->getMeta('Call')->get('request'));

            $this->haveProcessedRequest = true;
        }
        catch (\Exception $e)
        {
            $this->handleException($e);
        }
    }

    private function getCsrfToken()
    {
        $submittedCsrfToken = '';

        if (isset($_SERVER['HTTP_X_TOKEN']))
            $submittedCsrfToken = $_SERVER['HTTP_X_TOKEN'];
        elseif (isset($_REQUEST['token']))
            $submittedCsrfToken = $_REQUEST['token'];

        return (string)$submittedCsrfToken;
    }

    public function executeCall()
    {
        if ($this->getSuccess() === null)
        {
            try
            {
                if (!$this->haveProcessedRequest)
                    throw new InternalErrorException("The request object must be set before executing the call.");

                $this->response = call_user_func([$this, $this->getMeta('Call')->get('method')], $this->request);

                $this->setSuccess(true);
            }
            catch (\Exception $e)
            {
                $this->handleException($e);
            }
        }
    }

    private function checkAuthorisation()
    {
        $reqCapibilty = $this->getMeta('Security')->get('capability');

        if (is_null($reqCapibilty))
            throw new InternalErrorException("The endpoint call must specify the required capabilities.");

        if ($reqCapibilty)
        {
            $user = $this->getCurrentUser();

            if (!$user)
                throw new UnauthorizedException(Translate::t('You must be logged in to perform this operation.'));

            if (!$user->hasCapability($reqCapibilty))
                throw new UnauthorizedException(Translate::t("You do not have sufficient permissions to perform this operation."));
        }
    }


    // some helpers

    protected function getService($serviceName)
    {
        return $this->container->get($serviceName);
    }

    protected function getParameter($paramName)
    {
        return $this->container->getParameter($paramName);
    }

    protected function getCurrentUser()
    {
        return $this->container->has('agit.user')
            ? $this->container->get('agit.user')->getCurrentUser()
            : null;
    }

    protected function createObject($name, $data = null)
    {
        if (strpos($name, '/') === false)
            $name = $this->getMeta('Call')->get('namespace') . "/$name";

        return $this->container->get('agit.api.object')->createObject($name, $data);
    }

    private function handleException(\Exception $e)
    {
        $this->setSuccess(false);

        $code = ($e instanceof AgitException) ? $e->getErrorCode() : null;
        $message = ($e instanceof AgitException) ? $e->getMessage() : "Internal Error.";
//         $message = $e->getMessage();
        $this->addMessage('error', $message, $code);
    }
}
