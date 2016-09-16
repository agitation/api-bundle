<?php

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
 * A calendar day.
 */
class Date extends AbstractValueObject
{
    use DateTrait;

    public function fill($dateTime)
    {
        if ($dateTime instanceof \DateTime) {
            $this->year = (int) $dateTime->format("Y");
            $this->month = (int) $dateTime->format("m");
            $this->day = (int) $dateTime->format("d");
        }
    }

    public function __toString()
    {
        return sprintf("%04d-%02d-%02d", $this->year, $this->month, $this->day);
    }

    public function getDate()
    {
        return new \DateTime($this->__toString() . "00:00:00");
    }
}
