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
 * The client has requested to format the response in a format which is
 * incompatible with the data type of the result.
 */
class IncompatibleFormatterException extends ApiException
{
    protected $httpStatus = 406;
}
