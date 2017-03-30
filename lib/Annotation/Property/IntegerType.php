<?php

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
class IntegerType extends AbstractType
{
    protected $_isScalarType = true;

    protected $minValue = null;

    protected $maxValue = null;

    public function check($value)
    {
        $this->init($value);

        if ($this->mustCheck()) {
            static::$_validator->validate("integer", $value, $this->minValue, $this->maxValue);
        }
    }
}
