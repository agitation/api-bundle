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
 * A calendar day.
 */
class Date extends AbstractValueObject
{
    use DateTrait;

    public function fill($dateTime)
    {
        if ($dateTime)
        {
            $this->year = (int)$dateTime->format("Y");
            $this->month = (int)$dateTime->format("m");
            $this->day = (int)$dateTime->format("d");
        }
    }

    public function __toString()
    {
        return sprintf("%04d-%02d-%02d", $this->year, $this->month, $this->day);
    }
}
