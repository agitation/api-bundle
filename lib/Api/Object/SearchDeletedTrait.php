<?php

/*
 * @package    agitation/api-bundle
 * @link       http://github.com/agitation/api-bundle
 * @author     Alexander Günsche
 * @license    http://opensource.org/licenses/MIT
 */

namespace Agit\ApiBundle\Api\Object;

use Agit\ApiBundle\Annotation\Property;

trait SearchDeletedTrait
{
    /**
     * @Property\Name("Deleted")
     * @Property\BooleanType(nullable=true)
     *
     * Whether or not to include deleted objects in a search.
     */
    public $deleted;
}
