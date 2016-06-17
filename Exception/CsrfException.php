<?php
/**
 * @package    agitation/api
 * @link       http://github.com/agitation/AgitApiBundle
 * @author     Alex Günsche <http://www.agitsol.com/>
 * @copyright  2012-2015 AGITsol GmbH
 * @license    http://opensource.org/licenses/MIT
 */

namespace Agit\ApiBundle\Exception;

use Agit\CommonBundle\Exception\AgitException;

/**
 * The CSRF check has failed, probably due to a missing or incorrect CSRF token.
 */
class CsrfException extends AgitException
{
    protected $httpStatus = 401;
}
