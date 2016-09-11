<?php

/*
 * @package    agitation/api-bundle
 * @link       http://github.com/agitation/api-bundle
 * @author     Alexander Günsche
 * @license    http://opensource.org/licenses/MIT
 */

namespace Agit\ApiBundle\Common\Property;

use Agit\ApiBundle\Annotation\Property;

trait DeletedTrait
{
    /**
     * @Property\Name("Deleted")
     * @Property\BooleanType(readonly=true)
     */
    public $deleted;
}
