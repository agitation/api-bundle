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
class EntityType extends ObjectType
{
    protected $class = null;

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
                static::$_ValidationService->validate('int', $value, 1);
            else
                parent::check($value);
        }
    }
}