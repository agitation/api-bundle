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
 * Day and time of day.
 */
class DateTime extends AbstractValueObject
{
    use DateTrait;
    use TimeTrait;

    public function __toString()
    {
        return sprintf('%04d-%02d-%02d %02d:%02d:00', $this->year, $this->month, $this->day, $this->hour, $this->minute);
    }

    public function fill($dateTime)
    {
        if ($dateTime instanceof \DateTime)
        {
            $this->year = (int) $dateTime->format('Y');
            $this->month = (int) $dateTime->format('m');
            $this->day = (int) $dateTime->format('d');
            $this->hour = (int) $dateTime->format('H');
            $this->minute = (int) $dateTime->format('i');
        }
    }

    public function getDate()
    {
        return new \DateTime($this->__toString());
    }
}
