<?php

/*
 * @package    agitation/api-bundle
 * @link       http://github.com/agitation/api-bundle
 * @author     Alexander Günsche
 * @license    http://opensource.org/licenses/MIT
 */

/**
 * @link       http://github.com/agitation/AgitApiBundle
 *
 * @author     Alex Günsche <http://www.agitsol.com/>
 * @copyright  2012-2015 AGITsol GmbH
 * @license    http://opensource.org/licenses/MIT
 */

namespace Agit\ApiBundle\Controller;

use Agit\ApiBundle\Exception\BadRequestException;
use Agit\BaseBundle\Exception\AgitException;
use Agit\BaseBundle\Exception\InternalErrorException;
use Agit\IntlBundle\Tool\Translate;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ApiController extends Controller
{
    public function callAction(Request $request, $namespace, $class, $method, $_ext)
    {
        $response = new Response();
        $isDev = ($this->container->getParameter("kernel.environment") === "dev");

        try {
            $this->setLocale();
            $this->checkHeaderAuth();

            $endpointService = $this->container->get("agit.api.endpoint");
            $formatterService = $this->container->get("agit.api.formatter");

            if (! $formatterService->formatExists($_ext)) {
                throw new BadRequestException("Invalid format.");
            }

            $endpoint = $endpointService->createEndpoint("$namespace/$class.$method", $request);

            if (! $isDev && ! $endpoint->getMeta("Security")->get("allowCrossOrigin")) {
                $this->container->get("agit.api.csrf")->checkToken($this->getCsrfToken());
            }

            $endpoint->setupEnvironment();

            if ($request->getMethod() !== "OPTIONS") {
                $endpoint->executeCall();

                $response = $this->container->get("agit.api.formatter")
                    ->getFormatter($_ext, $endpoint, $request)->getResponse();
            }

            if ($endpoint->getMeta("Security")->get("allowCrossOrigin")) {
                $response->headers->set("Access-Control-Allow-Origin", "*");
            }
        } catch (\Exception $e) {
            $publicException = $e instanceof AgitException && ! ($e instanceof InternalErrorException);

            $content = $isDev || $publicException
                ? $e->getMessage()
                : Translate::t("Sorry, there has been an internal error. The administrators have been notified and will fix this as soon as possible.");

            if ($isDev && ! $publicException) {
                $content .= "\n\n" . $e->getTraceAsString();
            }

            $response->setContent($content);
            $response->setStatusCode($publicException ? $e->getHttpStatus() : 500);
            $response->headers->set("Content-Type", "text/plain; charset=UTF-8", true);
        }

        $response->headers->set("Cache-Control", "no-cache, must-revalidate, max-age=0", true);
        $response->headers->set("Pragma", "no-store", true);

        return $response;
    }

    private function checkHeaderAuth()
    {
        if ($this->container->has("agit.user")) {
            $username = isset($_SERVER["HTTP_X_USER"]) ? $_SERVER["HTTP_X_USER"] : "";
            $password = isset($_SERVER["HTTP_X_PASSWORD"]) ? $_SERVER["HTTP_X_PASSWORD"] : "";

            if ($username && $password) {
                $this->container->get("agit.user")->authenticate($username, $password);
            }
        }
    }

    private function setLocale()
    {
        $localeService = $this->container->get("agit.intl.locale");

        $locale = (isset($_REQUEST["locale"]) && in_array($_REQUEST["locale"], $localeService->getAvailableLocales()))
            ? $_REQUEST["locale"]
            : "en_GB";

        $localeService->setLocale($locale);
    }

    private function getCsrfToken()
    {
        $submittedCsrfToken = "";

        if (isset($_SERVER["HTTP_X_TOKEN"])) {
            $submittedCsrfToken = $_SERVER["HTTP_X_TOKEN"];
        } elseif (isset($_REQUEST["token"])) {
            $submittedCsrfToken = $_REQUEST["token"];
        }

        return (string) $submittedCsrfToken;
    }
}
