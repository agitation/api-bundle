<?php

/*
 * @package    agitation/api-bundle
 * @link       http://github.com/agitation/api-bundle
 * @author     Alexander Günsche
 * @license    http://opensource.org/licenses/MIT
 */

namespace Agit\ApiBundle\Api\Object;

use Agit\ApiBundle\Annotation\Object;
use Agit\ApiBundle\Annotation\Property;

/**
 * @Object\Object(namespace="common.v1")
 */
class Location extends AbstractValueObject
{
    /**
     * @Property\Name("Latitude")
     * @Property\FloatType(minValue=-90, maxValue=90)
     */
    public $lat;

    /**
     * @Property\Name("Longitude")
     * @Property\FloatType(minValue=-180, maxValue=180)
     */
    public $lon;
}
