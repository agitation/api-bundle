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
 * The server was acting as a gateway or proxy and received an invalid response
 * from the upstream server.
 */
class BadGatewayException extends ApiException
{
    protected $httpStatus = 502;
}
