<?php

/*
 * @package    agitation/api-bundle
 * @link       http://github.com/agitation/api-bundle
 * @author     Alexander Günsche
 * @license    http://opensource.org/licenses/MIT
 */

/**
 * @link       http://github.com/agitation/AgitApiBundle
 *
 * @author     Alex Günsche <http://www.agitsol.com/>
 * @copyright  2012-2015 AGITsol GmbH
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
