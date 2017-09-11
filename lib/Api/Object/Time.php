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
 * The time of a day.
 */
class Time extends AbstractValueObject
{
    use TimeTrait;

    public function fill($dateTime)
    {
        $this->hour = (int) $dateTime->format('H');
        $this->minute = (int) $dateTime->format('i');
    }

    public function setMinutes($minutes)
    {
        $this->set('hour', floor($minutes / 60));
        $this->set('minute', $minutes % 60);
    }

    public function getMinutes()
    {
        return $this->get('hour') * 60 + $this->get('minute');
    }
}
