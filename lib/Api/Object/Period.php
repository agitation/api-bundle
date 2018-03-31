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
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

/**
 * @Object\Object(namespace="common.v1")
 *
 * A date period, consisting of a start date and an end date.
 */
class Period extends AbstractValueObject
{
    /**
     * @Property\ObjectType(class="Date")
     */
    public $from;

    /**
     * @Property\ObjectType(class="Date")
     */
    public $until;

    public function validate()
    {
        parent::validate();

        if ($this->from->getDate()->getTimestamp() > $this->until->getDate()->getTimestamp())
        {
            throw new BadRequestHttpException('The start date must be earlier than or equal to the end date.');
        }
    }
}
