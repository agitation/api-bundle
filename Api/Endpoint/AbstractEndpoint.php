<?php
/**
 * @package    agitation/api
 * @link       http://github.com/agitation/AgitApiBundle
 * @author     Alex Günsche <http://www.agitsol.com/>
 * @copyright  2012-2015 AGITsol GmbH
 * @license    http://opensource.org/licenses/MIT
 */

namespace Agit\ApiBundle\Api\Endpoint;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Agit\CoreBundle\Exception\AgitException;
use Agit\CoreBundle\Exception\InternalErrorException;
use Agit\ApiBundle\Api\Meta\MetaContainer;
use Agit\ApiBundle\Api\Object\AbstractObject;
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
    protected $Container;

    /**
     * @var MetaContainer instance.
     */
    protected $Meta;

    /**
     * @var shortcut to translation service.
     */
    protected $translate;

    /**
     * @var indicates whether the call was successful or not.
     */
    private $success = null;

    /**
     * @var list of messages, usually errors or warnings
     */
    private $MessageList = [];

    /**
     * @var raw request, only used for security checks
     */
    private $Request;

    /**
     * @var request object, passed by the controller
     */
    private $RequestObject;

    /**
     * @var the generated response object
     */
    private $ResponseObject;

    public function __construct(ContainerInterface $Container, MetaContainer $Meta, Request $Request = null)
    {
        $this->Container = $Container;
        $this->Meta = $Meta;
        $this->Request = $Request;
        $this->translate = $Container->get('agit.intl.translate');
    }

    public function getMeta($name)
    {
        return $this->Meta->get($name);
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
        $this->MessageList[] = $this->createObject(
            'common.v1/Message',
            (object)['type' => $type, 'code' => $code, 'text' => $text]
        );
    }

    public function getMessages()
    {
        return $this->MessageList;
    }

    public function getResponse()
    {
        return $this->ResponseObject;
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

            if (!$this->Request)
                throw new InternalErrorException("The request object could not be created, as the actual request has not been passed to the endpoint.");

            $request = json_decode($this->Request->get('request'));

            if (!is_object($request))
                throw new BadRequestException($this->translate->t("The `request` parameter must contain a valid JSON object."));

            $this->RequestObject = $this->createObject(
                $this->getMeta('Call')->get('request'),
                $request);
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
                if (!$this->RequestObject)
                    throw new InternalErrorException("The request object must be set before executing the call.");

                $result = call_user_func([$this, $this->getMeta('Call')->get('method')], $this->RequestObject);
                $ResponseObject = $this->createResponse($result);

                // only set this after security is checked
                $this->ResponseObject = $ResponseObject;
                $this->setSuccess(true);
            }
            catch (\Exception $e)
            {
                $this->handleException($e);
            }
        }
    }

    /**
     * Generic helper to transform responses; covers most cases. Overload only
     * if you're doing something *very* special, e.g. filling objects with
     * extra data they need from context, otherwise trust the response objects.
     */
    protected function createResponse($result)
    {
        $ResponseObject = null;

        if ($result instanceof AbstractObject)
        {
            $ResponseObject = $result;
        }
        elseif (is_array($result) && $this->getMeta('Call')->get('listobject'))
        {
            $ResponseObject = $this->createObject($this->getMeta('Call')->get('response'));

            foreach ($result as $object)
            {
                $item = $this->createObject($this->getMeta('Call')->get('listobject'), $object);
                $ResponseObject->add('itemList', $item);
            }
        }
        elseif (is_object($result))
        {
            $ResponseObject = $this->createObject(
                $this->getMeta('Call')->get('response'),
                $result);
        }

        return $ResponseObject;
    }

    private function checkAuthorisation()
    {
        $reqCapibilty = $this->getMeta('Security')->get('capability');

        if (is_null($reqCapibilty))
            throw new InternalErrorException("The endpoint call must specify the required capabilities.");

        if ($reqCapibilty)
        {
            $User = $this->getCurrentUser();

            if (!$User)
                throw new UnauthorizedException($this->translate->t('You must be logged in to perform this operation.'));

            if (!$User->hasCapability($reqCapibilty))
                throw new UnauthorizedException($this->translate->t("You do not have sufficient permissions to perform this operation."));
        }
    }


    // some helpers

    protected function getService($serviceName)
    {
        return $this->Container->get($serviceName);
    }

    protected function getParameter($paramName)
    {
        return $this->Container->getParameter($paramName);
    }

    protected function getCurrentUser()
    {
        return $this->Container->has('agit.user')
            ? $this->Container->get('agit.user')->getCurrentUser()
            : null;
    }

    protected function createObject($name, $data = null)
    {
        if (strpos($name, '/') === false)
            $name = $this->getMeta('Call')->get('namespace') . "/$name";

        return $this->Container->get('agit.api.object')->createObject($name, $data);
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
