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
class EntityListType extends EntityType
{
    protected $minLength = null;

    protected $maxLength = null;

    protected $_isListType = true;

    public function check($value)
    {
        $this->init($value);

        if ($this->mustCheck()) {
            static::$_ValidationService->validate('array', $value);

            foreach ($value as $val) {
                static::$_ValidationService->validate($this->keytype, $val, 1);
            }
        }
    }
}
