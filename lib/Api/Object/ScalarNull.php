<?php

/*
 * @package    agitation/api-bundle
 * @link       http://github.com/agitation/api-bundle
 * @author     Alexander Günsche
 * @license    http://opensource.org/licenses/MIT
 */

namespace Agit\ApiBundle\Api\Object;

use Agit\ApiBundle\Annotation\Object;

/**
 * @Object\Object(namespace="common.v1")
 *
 * Acts as a pseudo-object, indicating an empty request or response. If a call
 * has this as its response object, the client should rely on the HTTP status
 * to see if the call was successful.
 */
class ScalarNull extends AbstractValueObject
{
}
