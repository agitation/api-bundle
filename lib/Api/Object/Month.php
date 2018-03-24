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

/**
 * @Object\Object(namespace="common.v1")
 *
 * A calendar month.
 */
class Month extends AbstractValueObject
{
    use MonthTrait;
}
