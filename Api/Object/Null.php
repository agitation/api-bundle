<?php
/**
 * @package    agitation/api
 * @link       http://github.com/agitation/AgitApiBundle
 * @author     Alex Günsche <http://www.agitsol.com/>
 * @copyright  2012-2015 AGITsol GmbH
 * @license    http://opensource.org/licenses/MIT
 */

namespace Agit\ApiBundle\Api\Object;

/**
 * Just an empty response payload container. If a call has this object as its
 * response object, you should simple rely on the `Response.status` field to see
 * if your call was successful.
 */
class Null extends AbstractObject
{
}
