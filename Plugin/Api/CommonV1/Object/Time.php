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
use Agit\ApiBundle\Common\AbstractValueObject;

/**
 * @Object\Object
 *
 * The time of a day.
 */
class Time extends AbstractValueObject
{
    use TimeTrait;

    public function fill($dateTime)
    {
        $this->hour = (int)$dateTime->format("H");
        $this->minute = (int)$dateTime->format("i");
    }

    public function setMinutes($minutes)
    {
        $this->set("hour", floor($minutes / 60));
        $this->set("minute", $minutes % 60);
    }

    public function getMinutes()
    {
        return $this->get("hour") * 60 + $this->get("minute");
    }
}
