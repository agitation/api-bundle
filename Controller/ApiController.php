<?php
/**
 * @package    agitation/api
 * @link       http://github.com/agitation/AgitApiBundle
 * @author     Alex Günsche <http://www.agitsol.com/>
 * @copyright  2012-2015 AGITsol GmbH
 * @license    http://opensource.org/licenses/MIT
 */

namespace Agit\ApiBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Agit\ApiBundle\Exception\BadRequestException;


class ApiController extends Controller
{
    public function callAction($namespace, $class, $method, $_format)
    {
        $request = $this->getRequest();
        $response = new Response();

        try
        {
            $this->setLocale();
            $this->checkHeaderAuth();

            $endpointService = $this->container->get('agit.api.endpoint');
            $formatterService = $this->container->get('agit.api.formatter');

            if (!$formatterService->formatExists($_format))
                throw new BadRequestException("Invalid format.");

            $endpoint = $endpointService->createEndpoint("$namespace/$class.$method", $request);

            if (!$endpoint->getMeta('Security')->get('allowCrossOrigin'))
                $this->container->get('agit.api.csrf')->checkToken($this->getCsrfToken());

            $endpoint->setupEnvironment();

            if ($request->getMethod() !== 'OPTIONS')
            {
                $endpoint->executeCall();

                $response = $this->container->get('agit.api.formatter')
                    ->getFormatter($_format, $endpoint, $request)->getResponse();
            }

            if ($endpoint->getMeta('Security')->get('allowCrossOrigin'))
                $response->headers->set('Access-Control-Allow-Origin', "*");
        }
        catch (\Exception $e)
        {
            // NOTE: Exceptions thrown during `executeCall` are caught by the endpoint itself
            // and transformed into a proper API response, so this one is just for edge cases.
            $response->setContent($e->getMessage());
            $response->headers->set("Content-Type", "text/html; charset=UTF-8", true);
        }

        $response->headers->set("Cache-Control", "no-cache, must-revalidate, max-age=0", true);
        $response->headers->set("Pragma", "no-store", true);

        return $response;
    }

    private function checkHeaderAuth()
    {
        if ($this->container->has('agit.user'))
        {
            $username = isset($_SERVER['HTTP_X_USER']) ? $_SERVER['HTTP_X_USER'] : '';
            $password = isset($_SERVER['HTTP_X_PASSWORD']) ? $_SERVER['HTTP_X_PASSWORD'] : '';

            if ($username && $password)
                $this->container->get('agit.user')->authenticate($username, $password);
        }
    }

    private function setLocale()
    {
        $localeService = $this->container->get('agit.intl.locale');

        $locale = (isset($_REQUEST['locale']) && in_array($_REQUEST['locale'], $localeService->getAvailableLocales()))
            ? $_REQUEST['locale']
            : 'en_GB';

        $localeService->setLocale($locale);
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
}
