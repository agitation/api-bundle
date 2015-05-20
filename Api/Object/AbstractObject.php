<?php
/**
 * @package    agitation/api
 * @link       http://github.com/agitation/AgitApiBundle
 * @author     Alex Günsche <http://www.agitsol.com/>
 * @copyright  2012-2015 AGITsol GmbH
 * @license    http://opensource.org/licenses/MIT
 */

namespace Agit\ApiBundle\Api\Object;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Agit\CoreBundle\Helper\StringHelper;
use Agit\CoreBundle\Exception\InternalErrorException;
use Agit\ApiBundle\Exception\InvalidObjectException;
use Agit\ApiBundle\Exception\InvalidObjectValueException;
use Agit\ApiBundle\Api\Meta\MetaContainer;
use Agit\ApiBundle\Api\Meta\Property\AbstractType;
use Agit\ApiBundle\Api\Meta\Property\Name;


abstract class AbstractObject implements \JsonSerializable
{
    /**
     * @var service container instance.
     */
    protected $Container;

    /**
     * @var instance of translator.
     */
    protected $translate;

    /**
     * @var MetaContainer instance for the object.
     */
    protected $ObjectMetaContainer;

    /**
     * @var MetaContainer instance for the properties.
     */
    protected $PropMetaContainerList = [];

    /**
     * @var API object name with namespace prefix, e.g. `common.v1/SomeObject`
     */
    protected $objectName;

    public function __construct(ContainerInterface $Container, MetaContainer $ObjectMetaContainer, array $PropMetaContainerList, $objectName)
    {
        $this->Container = $Container;
        $this->ObjectMetaContainer = $ObjectMetaContainer;
        $this->PropMetaContainerList = $PropMetaContainerList;
        $this->objectName = $objectName;
        $this->translate = $Container->get('agit.intl.translate');
    }

    public function getObjectName()
    {
        return $this->objectName;
    }

    public function hasProperty($key)
    {
        return isset($this->PropMetaContainerList[$key]);
    }

    public function get($key)
    {
        $this->checkHasProperty($key);
        return $this->$key;
    }

    public function getValues()
    {
        $values = [];

        foreach ($this->PropMetaContainerList as $key => $Meta)
            $values[$key] = $this->$key;

        return $values;
    }

    public function set($key, $value)
    {
        $this->checkHasProperty($key);
        $this->validateValue($key, $value);
        $this->$key = $value;
    }

    public function add($key, $value)
    {
        $this->checkHasProperty($key);
        $Type = $this->getPropertyMeta($key, 'Type');

        if ($Type->isListType())
        {
            $this->validateValue($key, [$value]);

            if (!is_array($this->$key))
                $this->$key = [];

            array_push($this->$key, $value);
        }
        elseif ($Type->getType() === 'number')
        {
            $this->validateValue($key, $value);
            $this->$key += $value;
        }
        else
        {
            throw new InternalErrorException("Cannot use 'add' with this property type.");
        }
    }
    public function getMeta($name)
    {
        return $this->Meta->get($name);
    }

    public function getPropertyMeta($propKey, $metaName)
    {
        $this->checkHasProperty($propKey);
        return $this->PropMetaContainerList[$propKey]->get($metaName);
    }

    protected function checkHasProperty($key)
    {
        if (!$this->hasProperty($key))
            throw new InvalidObjectException(sprintf(
                "The “%s” object does not have a “%s” property.",
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
        foreach ($this->PropMetaContainerList as $key => $MetaContainer)
            $this->validateValue($key, $this->$key);
    }
    protected function validateValue($key, $value)
    {
        try
        {
            $this->getPropertyMeta($key, 'Type')->check($value);
        }
        catch(\Exception $e)
        {
            throw new InvalidObjectValueException(sprintf(
                $this->translate->t("Invalid value for '%s': %s"),
                $this->getPropertyMeta($key, 'Name')->getName(), $e->getMessage()));
        }
    }
}
