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
class NumberType extends AbstractType
{
    protected $positive = false;

    protected $allowedValues = null;

    protected $allowFloat = false; // allow float values or not

    protected $minValue = null;

    protected $maxValue = null;

    protected $special = null;

    protected $_isScalarType = true;

    public function check($value)
    {
        $this->init($value);

        if ($this->mustCheck()) {
            if ($this->positive === true && (! $this->minValue || $this->minValue < 0)) {
                $this->minValue = 0;
            }

            static::$_validator->validate($this->allowFloat === true ? 'float' : 'integer', $value, $this->minValue, $this->maxValue);

            if (is_array($this->allowedValues)) {
                static::$_validator->validate('selection', $value, $this->allowedValues);
            }
        }
    }
}
