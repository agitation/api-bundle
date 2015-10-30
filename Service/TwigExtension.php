<?php
/**
 * @package    agitation/api
 * @link       http://github.com/agitation/AgitApiBundle
 * @author     Alex Günsche <http://www.agitsol.com/>
 * @copyright  2012-2015 AGITsol GmbH
 * @license    http://opensource.org/licenses/MIT
 */

namespace Agit\ApiBundle\Service;

use Agit\ApiBundle\Exception\CsrfException;
use Agit\CoreBundle\Service\UrlService;

class TwigExtension extends \Twig_Extension
{
    private $csrfTokenService;

    public function __construct(UrlService $urlService, CsrfTokenService $csrfTokenService)
    {
        $this->urlService = $urlService;
        $this->csrfTokenService = $csrfTokenService;
    }

    /**
     * name of the extension
     */
    public function getName()
    {
        return 'agit.api';
    }

    /**
     * registering the template function
     */
    public function getFunctions()
    {
        return [
            'getApiUrlBase' => new \Twig_Function_Method($this, 'getApiUrlBase'),
            'getCsrfToken' => new \Twig_Function_Method($this, 'getCsrfToken')
        ];
    }

    public function getCsrfToken()
    {
        return $this->csrfTokenService->getToken();
    }

    public function getApiUrlBase()
    {
        return $this->urlService->createBackendUrl('/api');
    }
}
