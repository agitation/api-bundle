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
class BooleanType extends AbstractType
{
    protected $_isScalarType = true;

    public function check($value)
    {
        $this->init($value);

        if ($this->mustCheck())
        {
            static::$_validator->validate('boolean', $value);
        }
    }
}
