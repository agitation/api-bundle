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
class FloatType extends AbstractType
{
    protected $_isScalarType = true;

    protected $minValue = null;

    protected $maxValue = null;

    public function init($value)
    {
        parent::init($value);
    }

    public function filter($value)
    {
        return (float) $value;
    }

    public function check($value)
    {
        $this->init($value);

        if ($this->mustCheck())
        {
            static::$_validator->validate('float', $value, $this->minValue, $this->maxValue);
        }
    }
}
