<?php
/**
 * @package    agitation/api
 * @link       http://github.com/agitation/AgitApiBundle
 * @author     Alex Günsche <http://www.agitsol.com/>
 * @copyright  2012-2015 AGITsol GmbH
 * @license    http://opensource.org/licenses/MIT
 */

namespace Agit\ApiBundle\Service;

use Symfony\Component\HttpFoundation\Session\Session;
use Agit\ApiBundle\Exception\CsrfException;
use Agit\CoreBundle\Helper\StringHelper;

class CsrfTokenService
{
    private $SessionService;

    const sessionKey = 'agit.api.csrf.token';

    public function __construct(Session $SessionService)
    {
        $this->SessionService = $SessionService;
    }

    public function initToken()
    {
        if (!$this->SessionService->get(self::sessionKey))
            $this->SessionService->set(self::sessionKey, StringHelper::createRandomString(25));
    }

    public function getToken()
    {
        $this->initToken();
        return $this->SessionService->get(self::sessionKey);
    }

    public function checkToken($submittedToken)
    {
            $correctCsrfToken = $this->SessionService->get(self::sessionKey);

            if (!$submittedToken || !$correctCsrfToken || $submittedToken !== $correctCsrfToken)
                throw new CsrfException("The CSRF token is invalid.");
    }
}
