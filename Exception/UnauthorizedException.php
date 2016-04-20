<?php
/**
 * @package    agitation/api
 * @link       http://github.com/agitation/AgitApiBundle
 * @author     Alex Günsche <http://www.agitsol.com/>
 * @copyright  2012-2015 AGITsol GmbH
 * @license    http://opensource.org/licenses/MIT
 */

namespace Agit\ApiBundle\Exception;

/**
 * The requested ressource requires authentication, but the client did not
 * authenticate and is therefore considered unauthorized. After authentication,
 * the client may try again.
 */
class UnauthorizedException extends ApiException
{
    protected $httpStatus = 401;
}
