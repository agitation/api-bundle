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
use Agit\ApiBundle\Common\AbstractValueObject;

/**
 * @Object\Object
 *
 * Day and time of day.
 */
class DateTime extends AbstractValueObject
{
    use DateTrait;
    use TimeTrait;

    public function fill($dateTime)
    {
        if ($dateTime instanceof \DateTime) {
            $this->year = (int) $dateTime->format("Y");
            $this->month = (int) $dateTime->format("m");
            $this->day = (int) $dateTime->format("d");
            $this->hour = (int) $dateTime->format("H");
            $this->minute = (int) $dateTime->format("i");
        }
    }

    public function __toString()
    {
        return sprintf("%04d-%02d-%02d %02d:%02d:00", $this->year, $this->month, $this->day, $this->hour, $this->minute);
    }

    public function getDate()
    {
        return new \DateTime($this->__toString());
    }
}
