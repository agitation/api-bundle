<?php
/**
 * @package    agitation/api
 * @link       http://github.com/agitation/AgitApiBundle
 * @author     Alex Günsche <http://www.agitsol.com/>
 * @copyright  2012-2015 AGITsol GmbH
 * @license    http://opensource.org/licenses/MIT
 */

namespace Agit\ApiBundle\Plugin\Api\CommonV1\Object;

use Agit\BaseBundle\Exception\InternalErrorException;
use Agit\ApiBundle\Annotation\Object;
use Agit\ApiBundle\Annotation\Property;
use Agit\ApiBundle\Common\AbstractResponseObject;

/**
 * @Object\Object
 *
 * The root response object. The response object you'll see in an endpoint
 * call's documentation is actually the `payload` field of this object.
 */
class Response extends AbstractResponseObject
{
    /**
     * @Property\PolymorphicType
     *
     * The actual response object, as specified in the endpoint call.
     */
    protected $payload = null;

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
    protected $entityList = [];

    public function setPayload($payload)
    {
        $this->payload = $payload;
    }

    public function setEntityList(array $entityList)
    {
        $this->entityList = $entityList;
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
