<?php

/*
 * @package    agitation/api-bundle
 * @link       http://github.com/agitation/api-bundle
 * @author     Alexander Günsche
 * @license    http://opensource.org/licenses/MIT
 */

namespace Agit\ApiBundle\Api\Object;

use Agit\ApiBundle\Annotation\Property;

trait IdTrait
{
    /**
     * @Property\Name("ID")
     * @Property\NumberType(positive=true, nullable=true)
     */
    public $id;
}