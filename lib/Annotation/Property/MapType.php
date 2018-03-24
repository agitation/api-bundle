<?php
declare(strict_types=1);

/*
 * @package    agitation/api-bundle
 * @link       http://github.com/agitation/api-bundle
 * @author     Alexander Günsche
 * @license    http://opensource.org/licenses/MIT
 */

namespace Agit\ApiBundle\Annotation\Property;

/**
 * @Annotation
 */
class MapType extends ArrayType
{
    protected function checkKey($arrayKey)
    {
        // associative array: keys must be string values
        static::$_validator->validate('string', $arrayKey);
    }
}
