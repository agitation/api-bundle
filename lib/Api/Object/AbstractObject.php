<?php

/*
 * @package    agitation/api-bundle
 * @link       http://github.com/agitation/api-bundle
 * @author     Alexander Günsche
 * @license    http://opensource.org/licenses/MIT
 */

namespace Agit\ApiBundle\Api\Object;

use Agit\ApiBundle\Annotation\MetaContainer;
use Agit\ApiBundle\Annotation\Property\Name;
use Agit\ApiBundle\Exception\InvalidObjectException;
use Agit\ApiBundle\Exception\InvalidObjectValueException;
use Agit\ApiBundle\Service\ObjectMetaService;
use Agit\BaseBundle\Exception\InternalErrorException;
use Agit\IntlBundle\Tool\Translate;
use JsonSerializable;

abstract class AbstractObject implements JsonSerializable
{
    /**
     * @var MetaContainer instance for the object.
     */
    protected $objectMeta;

    /**
     * @var MetaContainer instance for the properties.
     */
    protected $propertyMetas = [];

    /**
     * @var API object name with namespace prefix, e.g. `common.v1/SomeObject`
     */
    protected $objectName;

    /**
     * @var API namespace
     */
    protected $apiNamespace;

    /**
     * @var ObjectMetaService instance
     */
    private $objectMetaService;

    public function init($name, ObjectMetaService $objectMetaService)
    {
        $this->objectName = $name;
        $this->apiNamespace = strstr($this->objectName, "/", true);
        $this->objectMetaService = $objectMetaService;
        $this->objectMeta = $objectMetaService->getObjectMetas($this->objectName);
        $this->propertyMetas = $objectMetaService->getObjectPropertyMetas($this->objectName);
    }

    public function getName()
    {
        return $this->objectMeta->get("Name")->getName();
    }

    public function getObjectName()
    {
        return $this->objectName;
    }

    public function getKeys()
    {
        return array_keys($this->propertyMetas);
    }

    public function has($key)
    {
        return isset($this->propertyMetas[$key]);
    }

    public function get($key)
    {
        $this->checkHasProperty($key);

        return $this->$key;
    }

    public function getValues()
    {
        $values = [];

        foreach ($this->propertyMetas as $key => $meta) {
            if (isset($this->$key)) {
                $values[$key] = $this->$key;
            }
        }

        return $values;
    }

    public function set($key, $value)
    {
        $this->checkHasProperty($key);
        $this->$key = $value;
    }

    public function add($key, $value)
    {
        $this->checkHasProperty($key);
        $type = $this->getPropertyMeta($key, "Type");

        if ($type->isListType()) {
            if (! is_array($this->$key)) {
                $this->$key = [];
            }

            array_push($this->$key, $value);
        } elseif ($type->getType() === "number") {
            $this->$key += $value;
        } else {
            throw new InternalErrorException("Cannot use `add` with this property type.");
        }
    }

    public function getPropertyMeta($propKey, $metaName)
    {
        $this->checkHasProperty($propKey);

        return $this->propertyMetas[$propKey]->get($metaName);
    }

    protected function checkHasProperty($key)
    {
        if (! $this->has($key)) {
            throw new InvalidObjectException(sprintf(
                Translate::t("The `%s` object does not have a `%s` property."),
                $this->getName(),
                $key
            ));
        }
    }

    public function jsonSerialize()
    {
        return $this->getValues();
    }

    public function validate()
    {
        foreach ($this->propertyMetas as $key => $metaContainer) {
            $this->validateValue($key, $this->$key);
        }
    }

    protected function validateValue($key, $value)
    {
        try {
            $this->getPropertyMeta($key, "Type")->check($value);
        } catch (\Exception $e) {
            throw new InvalidObjectValueException(sprintf(
                Translate::t("Invalid value for “%s“: %s"),
                $this->getPropertyMeta($key, "Name")->getName(), $e->getMessage()));
        }
    }

    protected function createObject($name)
    {
        if (strpos($name, '/') === false) {
            $name = "{$this->apiNamespace}/$name";
        }

        return $this->objectMetaService->createObject($name);
    }
}
