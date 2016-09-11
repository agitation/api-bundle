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

namespace Agit\ApiBundle\Annotation\Property;

/**
 * @Annotation
 */
class MapType extends ArrayType
{
    protected function checkKey($arrayKey)
    {
        // associative array: keys must be string values
        static::$_ValidationService->validate('string', $arrayKey);
    }
}
