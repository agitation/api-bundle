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
    private $CsrfTokenService;

    public function __construct(UrlService $UrlService, CsrfTokenService $CsrfTokenService)
    {
        $this->UrlService = $UrlService;
        $this->CsrfTokenService = $CsrfTokenService;
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
        return $this->CsrfTokenService->getToken();
    }

    public function getApiUrlBase()
    {
        return $this->UrlService->createBackendUrl('/api');
    }
}
