<?php
/**
 * @package    agitation/api
 * @link       http://github.com/agitation/AgitApiBundle
 * @author     Alex Günsche <http://www.agitsol.com/>
 * @copyright  2012-2015 AGITsol GmbH
 * @license    http://opensource.org/licenses/MIT
 */

namespace Agit\ApiBundle\Api\Meta\Property;

use Agit\ApiBundle\Api\Meta\AbstractMeta;
use Agit\ApiBundle\Exception\InvalidObjectValueException;
use Agit\CoreBundle\Exception\InternalErrorException;

abstract class AbstractType extends AbstractMeta
{
    protected static $_ValidationService;

    /**
     * @var the annotated field may be `null` or unset
     */
    protected $nullable = false;

    /**
     * @var can be set to the name of an entity getter to indicate a data source
     */
    protected $source = null;

    /**
     * @var the annotated field must not be set in a request object
     */
    protected $readonly = false;

    /**
     * @var namespace of the referenced object
     */
    protected $objNamespace;

    /**
     * @var class name of the referenced object
     */
    protected $objClass;

    /**
     * @var referenced property
     */
    protected $objProperty;

    protected $_isScalarType = false;

    protected $_isListType = false;

    protected $_isObjectType = false;

    protected $_validate;

    // makes life easier when nullable===true
    private $_mustCheck = true;

    public static function setValidationService($validationService)
    {
        self::$_ValidationService = $validationService;
    }

    public function getSpecialFormatDesc($field)
    {
        if (!isset($this->_specialFormats))
            throw new InternalErrorException("special formats don't exist for this type.");

        if (!isset($this->_specialFormats[$field]))
            throw new InternalErrorException("The field $field doesn't exist for this type.");

        return $this->_specialFormats[$field];
    }

    public function setReference($namespace, $class, $property)
    {
        $this->objNamespace = $namespace;
        $this->objClass = $class;
        $this->objProperty = $property;
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

    protected function init($value)
    {
        if (!static::$_ValidationService)
            throw new InternalErrorException("The container must be set.");

        if ($this->readonly)
        {
            // readonly implies nullable
            $this->nullable = true;

//             if ($this->_direction === 'in' && isset($value))
//                 throw new InvalidObjectValueException("The value is read-only and hence must not be set in a request.");
        }

        if (!$this->nullable)
            static::$_ValidationService->validate('notNull', $value);

        if ($this->nullable === true && is_null($value))
            $this->_mustCheck = false;
    }

    protected function mustCheck()
    {
        return $this->_mustCheck;
    }
}
