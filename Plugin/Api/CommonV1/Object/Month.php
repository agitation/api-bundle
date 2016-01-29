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
 * A calendar month.
 */
class Month extends AbstractObject
{
    /**
     * @Property\Name("Month")
     * @Property\NumberType(minValue=1, maxValue=12)
     */
    public $month;

    /**
     * @Property\Name("Year")
     * @Property\NumberType(minValue=2000, maxValue=2100)
     */
    public $year;
}
