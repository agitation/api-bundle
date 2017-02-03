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
class ArrayType extends AbstractType
{
    protected $type = null;

    protected $allowedValues = null;

    protected $minLength = null;

    protected $maxLength = null;

    protected $_isListType = true;

    public function check($value)
    {
        $this->init($value);

        if ($this->mustCheck()) {
            static::$_validator->validate("array", $value, $this->minLength, $this->maxLength);

            if (is_array($this->allowedValues)) {
                static::$_validator->validate("multiSelection", $value, $this->allowedValues);
            }

            foreach ($value as $k => $val) {
                $this->checkKey($k);

                if ($this->type === "integer") {
                    static::$_validator->validate("integer", $val);
                } elseif ($this->type === "string") {
                    static::$_validator->validate("string", $val);
                } elseif ($this->type === "float") {
                    static::$_validator->validate("float", $val);
                } elseif ($this->type === "boolean") {
                    static::$_validator->validate("boolean", $val);
                } else {
                    static::$_validator->validate("scalar", $val);
                }
            }
        }
    }

    protected function checkKey($arrayKey)
    {
        // numeric array: keys must be integer values
        static::$_validator->validate("numeric", $arrayKey);
    }
}
