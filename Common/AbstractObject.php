<?php
/**
 * @package    agitation/api
 * @link       http://github.com/agitation/AgitApiBundle
 * @author     Alex Günsche <http://www.agitsol.com/>
 * @copyright  2012-2015 AGITsol GmbH
 * @license    http://opensource.org/licenses/MIT
 */

namespace Agit\ApiBundle\Common;

use Agit\CommonBundle\Exception\InternalErrorException;
use Agit\IntlBundle\Translate;
use Agit\ApiBundle\Exception\InvalidObjectException;
use Agit\ApiBundle\Exception\InvalidObjectValueException;
use Agit\ApiBundle\Annotation\MetaContainer;
use Agit\ApiBundle\Annotation\Property\AbstractType;
use Agit\ApiBundle\Annotation\Property\Name;
use Agit\ApiBundle\Service\ObjectMetaService;

abstract class AbstractObject implements \JsonSerializable
{
    /**
     * @var MetaContainer instance for the object.
     */
    protected $objectMeta;

    /**
     * @var MetaContainer instance for the properties.
     */
    protected $propMetaList = [];

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

    public function __construct(MetaContainer $objectMeta, array $propMetaList, ObjectMetaService $objectMetaService)
    {
        $this->objectMeta = $objectMeta;
        $this->propMetaList = $propMetaList;
        $this->objectMetaService = $objectMetaService;
        $this->objectName = $objectMeta->get("Object")->get("objectName");
        $this->apiNamespace = strstr($this->objectName, "/", true);
    }

    public function getObjectName()
    {
        return $this->objectName;
    }

    public function getKeys()
    {
        return array_keys($this->propMetaList);
    }

    public function has($key)
    {
        return isset($this->propMetaList[$key]);
    }

    public function get($key)
    {
        $this->checkHasProperty($key);
        return $this->$key;
    }

    public function getValues()
    {
        $values = [];

        foreach ($this->propMetaList as $key => $meta)
        {
            $metaField = $meta->get("Type")->get("meta");

            if ($metaField)
            {
                if ($metaField === "class")
                    $values[$key] = $this->objectName;
            }
            elseif (isset($this->$key)) // filter null and unset values
            {
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

        if ($type->isListType())
        {
            if (!is_array($this->$key))
                $this->$key = [];

            array_push($this->$key, $value);
        }
        elseif ($type->getType() === "number")
        {
            $this->$key += $value;
        }
        else
        {
            throw new InternalErrorException("Cannot use `add` with this property type.");
        }
    }

    public function getPropertyMeta($propKey, $metaName)
    {
        $this->checkHasProperty($propKey);
        return $this->propMetaList[$propKey]->get($metaName);
    }

    protected function checkHasProperty($key)
    {
        if (!$this->has($key))
            throw new InvalidObjectException(sprintf(
                Translate::t("The `%s` object does not have a `%s` property."),
                $this->getObjectName(),
                $key
            ));
    }

    public function jsonSerialize()
    {
        return $this->getValues();
    }

    public function validate()
    {
        foreach ($this->propMetaList as $key => $metaContainer)
            $this->validateValue($key, $this->$key);
    }

    protected function validateValue($key, $value)
    {
        try
        {
            $this->getPropertyMeta($key, "Type")->check($value);
        }
        catch(\Exception $e)
        {
            throw new InvalidObjectValueException(sprintf(
                Translate::t("Invalid value in object `%s` for property `%s`: %s"),
                $this->getObjectName(), $this->getPropertyMeta($key, "Name")->getName(), $e->getMessage()));
        }
    }

    protected function createObject($name)
    {
        if (strpos($name, '/') === false)
            $name = "{$this->apiNamespace}/$name";

        return $this->objectMetaService->createObject($name);
    }
}
