<?php
declare(strict_types=1);

/*
 * @package    agitation/api-bundle
 * @link       http://github.com/agitation/api-bundle
 * @author     Alexander Günsche
 * @license    http://opensource.org/licenses/MIT
 */

namespace Agit\ApiBundle\Service;

use Agit\BaseBundle\Tool\StringHelper;
use Symfony\Component\HttpFoundation\Session\Session;

use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class CsrfTokenService
{
    const sessionKey = 'agit.api.csrf.token';
    private $sessionService;

    public function __construct(Session $sessionService)
    {
        $this->sessionService = $sessionService;
    }

    public function initToken()
    {
        if (! $this->sessionService->get(self::sessionKey))
        {
            $this->sessionService->set(self::sessionKey, StringHelper::createRandomString(25));
        }
    }

    public function getToken()
    {
        $this->initToken();

        return $this->sessionService->get(self::sessionKey);
    }

    public function checkToken($submittedToken)
    {
        $correctCsrfToken = $this->sessionService->get(self::sessionKey);

        if (! $submittedToken || ! $correctCsrfToken || $submittedToken !== $correctCsrfToken)
        {
            throw new BadRequestHttpException('The CSRF token is invalid.');
        }
    }
}
