<?php
/**
 * @package    agitation/api
 * @link       http://github.com/agitation/AgitApiBundle
 * @author     Alex Günsche <http://www.agitsol.com/>
 * @copyright  2012-2015 AGITsol GmbH
 * @license    http://opensource.org/licenses/MIT
 */

namespace Agit\ApiBundle\Api\Object;

use Agit\CommonBundle\Exception\InternalErrorException;
use Agit\ApiBundle\Api\Meta\Property;

/**
 * A message generated by the Tixys system for the user's attention.
 */
class Message extends AbstractObject
{
    /**
     * @Property\StringType(allowedValues={"info","success","warning","error"})
     *
     * There are different types of messages:
     *
     * - `error`: The requested operation has failed. Usually in combination
     * with a negative value in the `Response.status` field.
     * - `warning`: While the requested operation has most likely succeeded,
     * there is an important fact the user should take note of.
     * - `info`: A message that doesn't imply anything severe, just something
     * “nice to know”.
     * - `success`: The requested operation has been successfully performed.
     * This is redundant with the `Response.status` field, but may be used to
     * emphasize a modification and/or provide additional information.
     */
    public $type;

    /**
     * @Property\StringType(nullable=true)
     *
     * A Tixys error code, if available, otherwise `null`.
     */
    public $code;

    /**
     * @Property\Name("Message Text")
     * @Property\StringType
     *
     * A plain text message describing the event or fact that is the subject of
     * this message.
     */
    public $text;
}
