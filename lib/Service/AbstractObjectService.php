<?php

/*
 * @package    agitation/api-bundle
 * @link       http://github.com/agitation/api-bundle
 * @author     Alexander Günsche
 * @license    http://opensource.org/licenses/MIT
 */

namespace Agit\ApiBundle\Service;

use Agit\ApiBundle\Api\Object\AbstractObject;
use Agit\ApiBundle\Exception\InvalidObjectValueException;
use Agit\BaseBundle\Exception\InternalErrorException;
use Agit\IntlBundle\Tool\Translate;

abstract class AbstractObjectService
{
    protected $objectMetaService;

    public function __construct(ObjectMetaService $objectMetaService)
    {
        $this->objectMetaService = $objectMetaService;
    }

    protected function fill(AbstractObject $object, $data)
    {
        if (! is_object($data)) {
            throw new InternalErrorException("The `data` parameter must be an object.");
        }

        $values = get_object_vars($data) + $object->getValues();

        foreach ($values as $key => $value) {
            $typeMeta = $this->objectMetaService->getPropertyMeta($object->getObjectName(), $key, "Type");
            $object->set($key, $this->createFieldValue($typeMeta, $key, $value));
        }

        return $object;
    }

    protected function createFieldValue($typeMeta, $key, $value)
    {
        $result = null;
        $expectedType = $typeMeta->getType();

        if (is_scalar($value) || is_null($value) || $expectedType === "polymorphic") {
            $result = $value;
        } elseif (is_array($value)) {
            if ($typeMeta->isObjectType() && $typeMeta->isListType()) {
                $result = [];

                foreach ($value as $listValue) {
                    $childObj = $this->objectMetaService->createObject($typeMeta->getTargetClass());
                    $this->fill($childObj, $listValue);
                    $result[] = $childObj;
                }
            } elseif (in_array($expectedType, ["array", "entity", "entitylist"])) {
                $result = $value;
            } else {
                throw new InvalidObjectValueException(sprintf(Translate::t("Invalid value for the `%s` property."), $key));
            }
        } elseif (is_object($value)) {
            if ($expectedType === "map") {
                $result = (array) $value;
            } else {
                if (! $typeMeta->isObjectType()) {
                    throw new InvalidObjectValueException(sprintf(Translate::t("Invalid value for the `%s` property."), $key));
                }

                $result = $this->objectMetaService->createObject($typeMeta->getTargetClass());
                $this->fill($result, $value);
            }
        }

        return $typeMeta->filter($result);
    }
}
