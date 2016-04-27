<?php
/**
 * @package    agitation/api
 * @link       http://github.com/agitation/AgitApiBundle
 * @author     Alex Günsche <http://www.agitsol.com/>
 * @copyright  2012-2015 AGITsol GmbH
 * @license    http://opensource.org/licenses/MIT
 */

namespace Agit\ApiBundle\Plugin\Api\CommonV1\Object;

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
