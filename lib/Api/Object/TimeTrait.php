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

trait TimeTrait
{
    /**
     * @Property\IntegerType(minValue=0, maxValue=23)
     */
    public $hour;

    /**
     * @Property\IntegerType(minValue=0, maxValue=59)
     */
    public $minute;
}
