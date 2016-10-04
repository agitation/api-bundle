<?php

/*
 * @package    agitation/api-bundle
 * @link       http://github.com/agitation/api-bundle
 * @author     Alexander Günsche
 * @license    http://opensource.org/licenses/MIT
 */

namespace Agit\ApiBundle\Api\Object;

use Agit\ApiBundle\Annotation\Property;

trait SearchPeriodTrait
{
    /**
     * @Property\Name("Search period")
     * @Property\ObjectType(class="common.v1/Period", nullable=true)
     */
    public $period;
}
