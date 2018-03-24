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
class EntityType extends ObjectType
{
    protected $class;

    protected $_isEntityType = true;

    protected $_isObjectType = false;

    private $_keytype;

    public function check($value)
    {
        $this->init($value);

        if ($this->mustCheck())
        {
            static::$_validator->validate($this->getChildKeyType(), $value, 1);
        }
    }

    protected function getChildKeyType()
    {
        if (! $this->_keytype)
        {
            $type = self::$_objectMeta->getPropertyMeta($this->class, 'id', 'Type');
            $this->_keytype = ($type instanceof StringType) ? 'string' : 'integer';
        }

        return $this->_keytype;
    }
}
