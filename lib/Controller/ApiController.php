<?php
declare(strict_types=1);
/*
 * @package    agitation/api-bundle
 * @link       http://github.com/agitation/api-bundle
 * @author     Alexander Günsche
 * @license    http://opensource.org/licenses/MIT
 */

namespace Agit\ApiBundle\Controller;

use Agit\ApiBundle\Event\ApiRequestErrorEvent;
use Agit\ApiBundle\Event\ApiRequestSuccessEvent;
use Agit\ApiBundle\Exception\InvalidEndpointException;
use Exception;
use Locale;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ApiController extends Controller
{
    public function callAction(Request $request, $namespace, $class, $method, $_ext)
    {
        $time = microtime(true);
        $mem = memory_get_peak_usage(true);
        $eventDispatcher = $this->container->get('event_dispatcher');

        $response = new Response();
        $isDev = ($this->container->getParameter('kernel.environment') === 'dev');

        // we must init the variables we’re using for the success and error events
        $endpointName = $requestData = $resultData = null;

        try
        {
            $this->setLocale();
            $this->checkHeaderAuth($request);

            $formatter = $this->container->get('agit.api.formatter')->getFormatter($_ext);
            $endpointService = $this->container->get('agit.api.endpoint');
            $endpointName = "$namespace/$class.$method";

            $endpointMeta = $endpointService->getEndpointMetaContainer($endpointName);
            $controller = $endpointService->createEndpointController($endpointName);
            $requestObject = null;

            if (! $isDev && ! $endpointMeta->get('Security')->get('allowCrossOrigin'))
            {
                $this->container->get('agit.api.csrf')->checkToken($this->getCsrfToken($request));
            }

            if ($request->getMethod() !== 'OPTIONS')
            {
                $requestData = $this->createRequestObject(
                    $endpointMeta->get('Endpoint')->get('request'),
                    $request->get('request')
                );

                if (! is_callable([$controller, $method]))
                {
                    throw new InvalidEndpointException("The `$endpointName` controller does not have a `$method` method.");
                }

                $resultData = $controller->$method($requestData);
                $response = $formatter->createResponse($request, $resultData);
            }

            if ($endpointMeta->get('Security')->get('allowCrossOrigin'))
            {
                $response->headers->set('Access-Control-Allow-Origin', '*');
                $response->headers->set('Access-Control-Allow-Credentials', 'true');
            }

            $response->headers->set('Cache-Control', 'no-cache, must-revalidate, max-age=0', true);
            $response->headers->set('Pragma', 'no-store', true);

            $eventDispatcher->dispatch('agit.api.request.success', new ApiRequestSuccessEvent(
                $request,
                $response,
                $endpointName,
                $requestData,
                $resultData,
                microtime(true) - $time,
                memory_get_peak_usage(true) - $mem
            ));
        }
        catch (Exception $e)
        {
            $eventDispatcher->dispatch('agit.api.request.error', new ApiRequestErrorEvent(
                $request,
                $response,
                $endpointName,
                $requestData,
                $e,
                microtime(true) - $time,
                memory_get_peak_usage(true) - $mem
            ));

            throw $e;
        }

        return $response;
    }

    private function checkHeaderAuth($request)
    {
        if ($this->container->has('agit.user'))
        {
            $username = $request->headers->get('x-user', null, true);
            $password = $request->headers->get('x-password', null, true);

            if ($username && $password)
            {
                $this->container->get('agit.user')->authenticate($username, $password);
            }
        }
    }

    private function setLocale()
    {
        $localeService = $this->container->get('agit.intl.locale');
        $localeConfigService = $this->container->get('agit.intl.config');
        $userLocale = $localeService->getUserLocale();
        $activeLocales = $localeConfigService->getActiveLocales();
        $localeService->setLocale(in_array($userLocale, $activeLocales) ? $userLocale : ($activeLocales[0] ?? $localeService->getDefaultLocale()));
    }

    private function getCsrfToken(Request $request)
    {
        $submittedCsrfToken = $request->headers->get('x-token', '', true);

        if (! $submittedCsrfToken)
        {
            $submittedCsrfToken = $request->get('token', '');
        }

        return $submittedCsrfToken;
    }

    private function createRequestObject($objectName, $rawRequest)
    {
        $request = json_decode($rawRequest);

        // allow literal strings without quotes
        if ($request === null&& strlen($rawRequest))
        {
            $request = $rawRequest;
        }

        return $this->container->get('agit.api.request')
            ->createRequestObject($objectName, $request);
    }
}
