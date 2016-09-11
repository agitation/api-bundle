<?php

/*
 * @package    agitation/api-bundle
 * @link       http://github.com/agitation/api-bundle
 * @author     Alexander Günsche
 * @license    http://opensource.org/licenses/MIT
 */

namespace Agit\ApiBundle\Plugin\Api\CommonV1\Object;

use Agit\ApiBundle\Annotation\Object;
use Agit\ApiBundle\Common\AbstractValueObject;

/**
 * @Object\Object
 *
 * Acts as a pseudo-object, indicating an empty request or response. If a call
 * has this as its response object, the client should rely on the HTTP status
 * to see if the call was successful.
 */
class Null extends AbstractValueObject
{
}
