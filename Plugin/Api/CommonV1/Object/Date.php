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
use Agit\ApiBundle\Common\AbstractObject;

/**
 * @Object\Object
 *
 * A calendar day.
 */
class Date extends Month
{
    /**
     * @Property\Name("Day")
     * @Property\NumberType(minValue=1, maxValue=31)
     */
    public $day;

    public function __toString()
    {
        return sprintf("%04d-%02d-%02d", $this->year, $this->month, $this->day);
    }

    public function setDateTime(\DateTime $dateTime)
    {
        $this->set("day", (int)$dateTime->format("d"));
        $this->set("month", (int)$dateTime->format("m"));
        $this->set("year", (int)$dateTime->format("Y"));
    }

    public function getDateTime(\DateTimezone $timezone = null)
    {
        if (!$timezone)
            $timezone = new \DateTimezone("UTC");

        return new \DateTime($this->__toString(), $timezone);
    }
}
