<?php
/**
 * @package    agitation/api
 * @link       http://github.com/agitation/AgitApiBundle
 * @author     Alex Günsche <http://www.agitsol.com/>
 * @copyright  2012-2015 AGITsol GmbH
 * @license    http://opensource.org/licenses/MIT
 */

namespace Agit\ApiBundle\Api\Meta\Call;

use Agit\CoreBundle\Lib\Api\Exception\UnauthorizedException;
use Agit\CoreBundle\Exception\InternalErrorException;
use Agit\ApiBundle\Api\Meta\AbstractMeta;

/**
 * @Annotation
 */
class Security extends AbstractMeta
{
    /**
     * @var user capability required for this call.
     */
    protected $capability;

    /**
     * @var whether or not to allow cross-origin requests.
     */
    protected $allowCrossOrigin = false;

    public function crossOriginAllowed()
    {
        return $this->allowCrossOrigin;
    }

    public function checkCsrfToken($submittedToken)
    {
        if (!$this->allowCrossOrigin)
        {
            $correctCsrfToken = $this->getContainer()->get('session')->get('agit.csrfToken');

            if (!$submittedToken || !$correctCsrfToken || $submittedToken !== $correctCsrfToken)
                throw new UnauthorizedException("Incomplete request."); // be vague on purpose.
        }
    }

    public function checkAuthorisation()
    {
        if (is_null($this->capability))
            throw new InternalErrorException("The endpoint call must specify the required capabilities.");

        if ($this->capability !== '')
        {
            $User = $this->getContainer()->get('agit.user')->getCurrentUser();

            if (!$User)
                throw new UnauthorizedException($this->getContainer()->get('agit.intl.translate')->t("You must be logged in to perform this operation."));

            if (!$User->hasCap($this->capability))
                throw new UnauthorizedException($this->getContainer()->get('agit.intl.translate')->t("You do not have sufficient permissions to perform this operation."));
        }
    }
}
