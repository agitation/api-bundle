<?php
/**
 * @package    agitation/api
 * @link       http://github.com/agitation/AgitApiBundle
 * @author     Alex Günsche <http://www.agitsol.com/>
 * @copyright  2012-2015 AGITsol GmbH
 * @license    http://opensource.org/licenses/MIT
 */

namespace Agit\ApiBundle\Annotation\Property;

/**
 * @Annotation
 */
class ArrayType extends AbstractType
{
    protected $elemtype = null;

    protected $allowedValues = null;

    protected $minLength = null;

    protected $maxLength = null;

    protected $_isListType = true;

    public function check($value)
    {
        $this->init($value);

        if ($this->mustCheck())
        {
            static::$_ValidationService->validate('array', $value, $this->minLength, $this->maxLength);

            if (is_array($this->allowedValues))
                static::$_ValidationService->validate('multiSelection', $value, array_keys($this->allowedValues));

            foreach ($value as $k => $val)
            {
                $this->checkKey($k);

                if ($this->elemtype === 'integer')
                    static::$_ValidationService->validate('integer', $val);
                elseif ($this->elemtype === 'string')
                    static::$_ValidationService->validate('string', $val);
                elseif ($this->elemtype === 'float')
                    static::$_ValidationService->validate('float', $val);
                elseif ($this->elemtype === 'boolean')
                    static::$_ValidationService->validate('boolean', $val);
                else
                    static::$_ValidationService->validate('scalar', $val);
            }
        }
    }

    protected function checkKey($arrayKey)
    {
        // numeric array: keys must be integer values
        static::$_ValidationService->validate('numeric', $arrayKey);
    }
}
