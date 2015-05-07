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
            static::$_ValidationService->validate('array', $value, $this->minLength, $this->maxLength);

            foreach ($value as $val)
                $this->checkValue($val);
        }
    }
}
