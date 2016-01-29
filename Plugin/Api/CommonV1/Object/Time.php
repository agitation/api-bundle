<?php
/**
 * @package    agitation/api
 * @link       http://github.com/agitation/AgitApiBundle
 * @author     Alex Günsche <http://www.agitsol.com/>
 * @copyright  2012-2015 AGITsol GmbH
 * @license    http://opensource.org/licenses/MIT
 */

namespace Agit\ApiBundle\Plugin\Api\CommonV1\Object;

use Agit\ApiBundle\Annotation\Object;
use Agit\ApiBundle\Annotation\Property;
use Agit\ApiBundle\Common\AbstractObject;

/**
 * @Object\Object
 *
 * The time of a day.
 */
class Time extends AbstractObject
{
    /**
     * @Property\Name("Hour")
     * @Property\NumberType(minValue=0, maxValue=23)
     */
    public $hour;

    /**
     * @Property\Name("Minute")
     * @Property\NumberType(minValue=0, maxValue=59)
     */
    public $minute;
}
