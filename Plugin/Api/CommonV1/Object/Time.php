<?php
/**
 * @package    agitation/api
 * @link       http://github.com/agitation/AgitApiBundle
 * @author     Alex Günsche <http://www.agitsol.com/>
 * @copyright  2012-2015 AGITsol GmbH
 * @license    http://opensource.org/licenses/MIT
 */

namespace Agit\ApiBundle\Plugin\Api\CommonV1\Object;

use Agit\ApiBundle\Annotation\Object;
use Agit\ApiBundle\Annotation\Property;
use Agit\ApiBundle\Common\AbstractValueObject;

/**
 * @Object\Object
 *
 * The time of a day.
 */
class Time extends AbstractValueObject
{
    /**
     * @Property\Name("Hour")
     * @Property\NumberType(minValue=0, maxValue=23)
     */
    public $hour;

    /**
     * @Property\Name("Minute")
     * @Property\NumberType(minValue=0, maxValue=59)
     */
    public $minute;

    public function setMinutes($minutes)
    {
        $this->set("hour", floor($minutes / 60));
        $this->set("minute", $minutes % 60);
    }

    public function getMinutes()
    {
        return $this->get("hour") * 60 + $this->get("minute");
    }

    public function setDateTime(\DateTime $dateTime)
    {
        $this->set("hour", (int)$dateTime->format("H"));
        $this->set("minute", (int)$dateTime->format("i"));
    }

    public function getDateTime(\DateTimezone $timezone = null)
    {
        if (!$timezone)
            $timezone = new \DateTimezone("UTC");

        return new \DateTime(sprintf(
            "1970-01-01 %2d:%2d:00",
            $this->get("hour"),
            $this->get("minute")
        ), $timezone);
    }

}
