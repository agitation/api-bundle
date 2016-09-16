<?php

/*
 * @package    agitation/api-bundle
 * @link       http://github.com/agitation/api-bundle
 * @author     Alexander Günsche
 * @license    http://opensource.org/licenses/MIT
 */

namespace Agit\ApiBundle\Service;

use Agit\BaseBundle\Service\UrlService;
use Twig_Extension;
use Twig_SimpleFunction;

class TwigExtension extends Twig_Extension
{
    private $csrfTokenService;

    public function __construct(UrlService $urlService, CsrfTokenService $csrfTokenService)
    {
        $this->urlService = $urlService;
        $this->csrfTokenService = $csrfTokenService;
    }

    /**
     * name of the extension.
     */
    public function getName()
    {
        return "agit.api";
    }

    /**
     * registering the template function.
     */
    public function getFunctions()
    {
        return [
            new Twig_SimpleFunction("getApiUrlBase", [$this, "getApiUrlBase"]),
            new Twig_SimpleFunction("getCsrfToken", [$this, "getCsrfToken"])
        ];
    }

    public function getCsrfToken()
    {
        return $this->csrfTokenService->getToken();
    }

    public function getApiUrlBase()
    {
        return $this->urlService->createAppUrl("/api");
    }
}
