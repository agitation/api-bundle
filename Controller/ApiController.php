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
    public function mainAction($namespace, $endpoint, $call, $_format)
    {
        $Request = $this->getRequest();
        $Response = new Response();

        try
        {
            $this->setLocale();
            $this->checkHeaderAuth();

            $EndpointService = $this->container->get('agit.api.endpoint');
            $FormatterService = $this->container->get('agit.api.formatter');

            if (!$FormatterService->formatExists($_format))
                throw new BadRequestException("Invalid format.");

            $Endpoint = $EndpointService->createEndpoint("$namespace/$endpoint.$call", $Request);
            $Endpoint->setupEnvironment();

            if ($Request->getMethod() !== 'OPTIONS')
            {
                $Endpoint->executeCall();

                $Response = $this->container->get('agit.api.formatter')
                    ->getFormatter($_format, $Endpoint, $Request)->getResponse();
            }

            if ($Endpoint->getMeta('Security')->get('allowCrossOrigin'))
                $Response->headers->set('Access-Control-Allow-Origin', "*");
        }
        catch (\Exception $e)
        {
            // NOTE: Exceptions thrown during `executeCall` are caught by the endpoint itself
            // and transformed into a proper API response, so this one is just for edge cases.
            $Response->setContent($e->getMessage());
            $Response->headers->set("Content-Type", "text/html; charset=UTF-8", true);
        }

        $Response->headers->set("Cache-Control", "no-cache, must-revalidate, max-age=0", true);
        $Response->headers->set("Pragma", "no-store", true);

        return $Response;
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
        $LocaleService = $this->container->get('agit.intl.locale');

        $locale = (isset($_REQUEST['locale']) && in_array($_REQUEST['locale'], $LocaleService->getAvailableLocales()))
            ? $_REQUEST['locale']
            : 'en_GB';

        $LocaleService->setLocale($locale);
    }
}
