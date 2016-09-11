<?php

/*
 * @package    agitation/api-bundle
 * @link       http://github.com/agitation/api-bundle
 * @author     Alexander Günsche
 * @license    http://opensource.org/licenses/MIT
 */

/**
 * @link       http://github.com/agitation/AgitApiBundle
 *
 * @author     Alex Günsche <http://www.agitsol.com/>
 * @copyright  2012-2015 AGITsol GmbH
 * @license    http://opensource.org/licenses/MIT
 */

namespace Agit\ApiBundle\Plugin\Api\CommonV1\Object;

use Agit\ApiBundle\Annotation\Object;
use Agit\ApiBundle\Annotation\Property;
use Agit\ApiBundle\Common\AbstractValueObject;
use Agit\ApiBundle\Exception\InvalidRangeException;
use Agit\IntlBundle\Tool\Translate;

/**
 * @Object\Object
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

        if ($this->from->getDate()->getTimestamp() > $this->until->getDate()->getTimestamp()) {
            throw new InvalidRangeException(Translate::t("The start date must be earlier than or equal to the end date."));
        }
    }
}
