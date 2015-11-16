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
class EntityListType extends ObjectListType
{
    protected $class = null;

    protected $minLength = null;

    protected $maxLength = null;

    public function isObjectType()
    {
        return ($this->_direction === 'out');
    }

    public function check($value)
    {
        $this->init($value);

        if ($this->mustCheck())
        {
            if ($this->_direction === 'in')
            {
                static::$_ValidationService->validate('array', $value);

                foreach ($value as $val)
                    static::$_ValidationService->validate('integer', $val, 1);
            }
            else
            {
                parent::check($value);
            }
        }
    }
}
