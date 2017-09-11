<?php
declare(strict_types=1);
/*
 * @package    agitation/api-bundle
 * @link       http://github.com/agitation/api-bundle
 * @author     Alexander Günsche
 * @license    http://opensource.org/licenses/MIT
 */

namespace Agit\ApiBundle\Api\Object;

use Agit\ApiBundle\Annotation\Property;

trait MonthTrait
{
    /**
     * @Property\Name("Month")
     * @Property\IntegerType(minValue=1, maxValue=12)
     */
    public $month;

    /**
     * @Property\Name("Year")
     * @Property\IntegerType(minValue=2000, maxValue=2100)
     */
    public $year;
}
