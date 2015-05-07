<?php
/**
 * @package    agitation/api
 * @link       http://github.com/agitation/AgitApiBundle
 * @author     Alex Günsche <http://www.agitsol.com/>
 * @copyright  2012-2015 AGITsol GmbH
 * @license    http://opensource.org/licenses/MIT
 */

namespace Agit\ApiBundle\Api\Meta\Property;

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

        if ($this->mustCheck())
        {
            if ($this->positive === true && (!$this->minValue || $this->minValue < 0))
                $this->minValue = 0;

            $func = $this->allowFloat === true ? 'float' : 'int';
            static::$_ValidationService->validate($func, $value, $this->minValue, $this->maxValue);

            if (is_array($this->allowedValues))
                static::$_ValidationService->validate('selection', $value, $this->allowedValues);
        }
    }
}
