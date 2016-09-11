<?php

/*
 * @package    agitation/api-bundle
 * @link       http://github.com/agitation/api-bundle
 * @author     Alexander Günsche
 * @license    http://opensource.org/licenses/MIT
 */

namespace Agit\ApiBundle\Plugin\Api\CommonV1\Object;

use Agit\ApiBundle\Annotation\Object;
use Agit\ApiBundle\Annotation\Property;
use Agit\ApiBundle\Common\AbstractValueObject;

/**
 * @Object\Object
 */
class Location extends AbstractValueObject
{
    /**
     * @Property\Name("Latitude")
     * @Property\NumberType(allowFloat=true, minValue=-90, maxValue=90)
     */
    public $lat;

    /**
     * @Property\Name("Longitude")
     * @Property\NumberType(allowFloat=true, minValue=-180, maxValue=180)
     */
    public $lon;
}
