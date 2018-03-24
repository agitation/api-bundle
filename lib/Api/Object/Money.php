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
 * @Object\Object(namespace="common.v1")
 *
 * A monetary value consisting of the amount and th currency code.
 */
class Money extends AbstractValueObject
{
    /**
     * @Property\Name("Amount")
     * @Property\FloatType
     */
    public $amount;

    /**
     * @Property\Name("Currency")
     * @Property\StringType(minLength=3, maxLength=3)
     */
    public $currency;
}
