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
class EntityType extends ObjectType
{
    protected $keytype = "integer";

    protected $class;

    protected $_isEntityType = true;

    protected $_isObjectType = false;

    public function check($value)
    {
        $this->init($value);

        if ($this->mustCheck()) {
            static::$_ValidationService->validate($this->keytype, $value, 1);
        }
    }
}
