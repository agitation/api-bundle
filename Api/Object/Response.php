<?php
/**
 * @package    agitation/api
 * @link       http://github.com/agitation/AgitApiBundle
 * @author     Alex Günsche <http://www.agitsol.com/>
 * @copyright  2012-2015 AGITsol GmbH
 * @license    http://opensource.org/licenses/MIT
 */

namespace Agit\ApiBundle\Api\Object;

use Agit\CoreBundle\Exception\InternalErrorException;
use Agit\ApiBundle\Api\Meta\Property;

/**
 * The root response object. The response object you'll see in an endpoint
 * call's documentation is actually the `payload` field of this object.
 */
class Response extends AbstractObject
{
    /**
     * @Property\BooleanType
     *
     * The overall success status of the call. Note that this only indicates if
     * a request could be properly processed. For example, a valid search
     * request will always be considered successful, even if no results were
     * found.
     */
    public $success = null;

    /**
     * @Property\ObjectListType(class="Message")
     *
     * In case there are messages which the user must take note of, they are
     * added to this list.
     */
    public $messageList = array();

    /**
     * @Property\PolymorphicType
     *
     * The actual response object, as specified in the endpoint call.
     */
    public $payload = null;

    /**
     * @Property\PolymorphicType
     *
     * Ok, this one is a bit complicated: Tixys extracts objects with an ID from
     * the payload, stores them in this list, and then sets a reference from
     * their former position in the payload to their position in the list.
     *
     * Why do we do that? JSON and XML may become very redundant when an object
     * tree contains the same objects over and over again, as they don't know
     * how to cross-referencing the same object at various positions in a tree.
     * Therefore, we do it ourselves and save a lot of ressources.
     *
     * Sample code for unfolding this structure can be found in various places
     * in the `examples` section of the SDK.
     */
    public $entityList = array();

    public function setSuccess($value)
    {
        if (!is_bool($value))
            throw new InternalErrorException("The value must be boolean or null.");

        $this->success = $value;
    }

    public function setMessageList(array $messageList)
    {
        foreach ($messageList as $message)
        {
            if (!is_object($message) || !($message instanceof Message))
                throw new InternalErrorException("Invalid message object.");

            $this->messageList[] = $message;
        }
    }

    public function addMessage(\stdClass $message)
    {
        $this->messageList[] = $message;
    }

    public function setPayload($payload)
    {
        $this->payload = $payload;
    }

    public function setEntityList(array $entityList)
    {
        $this->entityList = $entityList;
    }

    public function getSuccess()
    {
        return $this->success;
    }

    public function getMessageList()
    {
        return $this->messageList;
    }

    public function getPayload()
    {
        return $this->payload;
    }

    public function getEntityList()
    {
        return $this->entityList;
    }
}
