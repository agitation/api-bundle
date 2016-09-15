<?php

/*
 * @package    agitation/api-bundle
 * @link       http://github.com/agitation/api-bundle
 * @author     Alexander Günsche
 * @license    http://opensource.org/licenses/MIT
 */

namespace Agit\ApiBundle\Api\Object;

use Agit\ApiBundle\Annotation\Property;

trait DateTrait
{
    use MonthTrait;

    /**
     * @Property\Name("Day")
     * @Property\NumberType(minValue=1, maxValue=31)
     */
    public $day;
}
