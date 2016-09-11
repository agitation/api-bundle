<?php

/*
 * @package    agitation/api-bundle
 * @link       http://github.com/agitation/api-bundle
 * @author     Alexander Günsche
 * @license    http://opensource.org/licenses/MIT
 */

/**
 * @link       http://github.com/agitation/AgitApiBundle
 *
 * @author     Alex Günsche <http://www.agitsol.com/>
 * @copyright  2012-2015 AGITsol GmbH
 * @license    http://opensource.org/licenses/MIT
 */

namespace Agit\ApiBundle\Annotation\Property;

use Agit\ApiBundle\Common\AbstractObject;
use Agit\ApiBundle\Exception\InvalidObjectValueException;

/**
 * @Annotation
 */
class ObjectType extends AbstractType
{
    /**
     * @var reference to the expected class, namespace may be omitted
     */
    protected $class = null;

    protected $_isObjectType = true;

    public function getTargetClass()
    {
        return $this->class;
    }

    public function check($value)
    {
        $this->init($value);

        if ($this->mustCheck()) {
            $this->checkValue($value);
        }
    }

    protected function checkValue($value)
    {
        static::$_ValidationService->validate('object', $value);

        if (! ($value instanceof AbstractObject) || $value->getObjectName() !== $this->class) {
            throw new InvalidObjectValueException(sprintf("The value must be a `%s` object.", $value->getObjectName()));
        }

        $value->validate();
    }
}
