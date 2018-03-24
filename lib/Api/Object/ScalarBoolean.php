<?php
declare(strict_types=1);

/*
 * @package    agitation/api-bundle
 * @link       http://github.com/agitation/api-bundle
 * @author     Alexander Günsche
 * @license    http://opensource.org/licenses/MIT
 */

namespace Agit\ApiBundle\Api\Object;

use Agit\ApiBundle\Annotation\Object;
use Agit\ApiBundle\Annotation\Property;

/**
 * @Object\Object(namespace="common.v1", scalar=true)
 *
 * A simple boolean value.
 */
class ScalarBoolean extends AbstractValueObject
{
    /**
     * @Property\Name("Value")
     * @Property\BooleanType
     */
    public $_;
}
