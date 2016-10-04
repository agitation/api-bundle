<?php

/*
 * @package    agitation/api-bundle
 * @link       http://github.com/agitation/api-bundle
 * @author     Alexander Günsche
 * @license    http://opensource.org/licenses/MIT
 */

namespace Agit\ApiBundle\Api\Object;

use Agit\ApiBundle\Annotation\Property;

trait SearchOrderTrait
{
    /**
     * @Property\Name("Order by field")
     * @Property\StringType(nullable=true)
     *
     * The field by which the result set should be ordered.
     */
    public $orderBy = "id";

    /**
     * @Property\Name("Order by field")
     * @Property\StringType(nullable=true, allowedValues={"asc", "desc"})
     *
     * The field by which the result set should be ordered.
     */
    public $orderDir = "asc";
}
