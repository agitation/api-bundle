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
class ObjectListType extends ObjectType
{
    protected $class = null;

    protected $minLength = null;

    protected $maxLength = null;

    protected $_isListType = true;

    protected $_isObjectType = true;

    public function check($value)
    {
        $this->init($value);

        if ($this->mustCheck())
        {
            static::$_validator->validate('array', $value, $this->minLength, $this->maxLength);

            foreach ($value as $val)
            {
                $this->checkValue($val);
            }
        }
    }
}
