<?php

/*
 * @package    agitation/api-bundle
 * @link       http://github.com/agitation/api-bundle
 * @author     Alexander Günsche
 * @license    http://opensource.org/licenses/MIT
 */

namespace Agit\ApiBundle\Annotation\Property;

use Agit\ApiBundle\Exception\InvalidObjectValueException;
use Agit\BaseBundle\Exception\InternalErrorException;

abstract class AbstractType extends AbstractPropertyMeta
{
    protected static $_ValidationService;

    /**
     * @var the annotated field may be `null` or unset
     */
    protected $nullable = false;

    /**
     * @var can be set to the name of a getter method to use, if the input is an object
     */
    protected $source = null;

    /**
     * @var if this field is not null, it marks the property as a meta field. the only possible value is currently `class`
     */
    protected $meta;

    /**
     * @var the annotated field must not be set in a request object
     */
    protected $readonly = false;

    protected $_isScalarType = false;

    protected $_isListType = false;

    protected $_isObjectType = false;

    protected $_isEntityType = false;

    protected $_validate;

    // makes life easier when nullable===true
    private $_mustCheck = true;

    public static function setValidationService($validationService)
    {
        self::$_ValidationService = $validationService;
    }

    public function getType()
    {
        return strtolower(substr(strrchr(get_class($this), "\\"), 1, -4));
    }

    public function isScalarType()
    {
        return $this->_isScalarType;
    }

    public function isListType()
    {
        return $this->_isListType;
    }

    public function isObjectType()
    {
        return $this->_isObjectType;
    }

    public function isEntityType()
    {
        return $this->_isEntityType;
    }

    protected function init($value)
    {
        if (! static::$_ValidationService) {
            throw new InternalErrorException("The container must be set.");
        }

        if ($this->readonly) {
            if (isset($value)) {
                throw new InvalidObjectValueException("The value is read-only and hence must not be set in a request.");
            }

            // readonly implies nullable
            $this->nullable = true;
        }

        if (! $this->nullable) {
            static::$_ValidationService->validate('notNull', $value);
        }

        if ($this->nullable === true && is_null($value)) {
            $this->_mustCheck = false;
        }
    }

    protected function mustCheck()
    {
        return $this->_mustCheck;
    }
}
