<?php
/**
 * @package    agitation/api
 * @link       http://github.com/agitation/AgitApiBundle
 * @author     Alex Günsche <http://www.agitsol.com/>
 * @copyright  2012-2015 AGITsol GmbH
 * @license    http://opensource.org/licenses/MIT
 */

namespace Agit\ApiBundle\Plugin\Api\CommonV1\Object;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Agit\CommonBundle\Helper\StringHelper;
use Agit\CommonBundle\Exception\InternalErrorException;
use Agit\IntlBundle\Translate;
use Agit\ApiBundle\Exception\InvalidObjectException;
use Agit\ApiBundle\Exception\InvalidObjectValueException;
use Agit\ApiBundle\Annotation\MetaContainer;
use Agit\ApiBundle\Annotation\Property\AbstractType;
use Agit\ApiBundle\Annotation\Property\Name;


abstract class AbstractObject implements \JsonSerializable
{
    /**
     * @var service container instance.
     */
    protected $container;

    /**
     * @var MetaContainer instance for the object.
     */
    protected $objectMetaContainer;

    /**
     * @var MetaContainer instance for the properties.
     */
    protected $propMetaContainerList = [];

    /**
     * @var API object name with namespace prefix, e.g. `common.v1/SomeObject`
     */
    protected $objectName;

    public function __construct(ContainerInterface $container, MetaContainer $objectMetaContainer, array $propMetaContainerList, $objectName)
    {
        $this->container = $container;
        $this->objectMetaContainer = $objectMetaContainer;
        $this->propMetaContainerList = $propMetaContainerList;
        $this->objectName = $objectName;
    }

    public function getObjectName()
    {
        return $this->objectName;
    }

    public function hasProperty($key)
    {
        return isset($this->propMetaContainerList[$key]);
    }

    public function get($key)
    {
        $this->checkHasProperty($key);
        return $this->$key;
    }

    public function getValues()
    {
        $values = [];

        foreach ($this->propMetaContainerList as $key => $meta)
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
        $type = $this->getPropertyMeta($key, 'Type');

        if ($type->isListType())
        {
            $this->validateValue($key, [$value]);

            if (!is_array($this->$key))
                $this->$key = [];

            array_push($this->$key, $value);
        }
        elseif ($type->getType() === 'number')
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
        return $this->meta->get($name);
    }

    public function hasPropertyMeta($propKey, $metaName)
    {
        $this->checkHasProperty($propKey);
        return $this->propMetaContainerList[$propKey]->has($metaName);
    }

    public function getPropertyMeta($propKey, $metaName)
    {
        $this->checkHasProperty($propKey);
        return $this->propMetaContainerList[$propKey]->get($metaName);
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
        foreach ($this->propMetaContainerList as $key => $metaContainer)
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
                Translate::t("Invalid value for “%s”: %s"),
                $this->getPropertyMeta($key, 'Name')->getName(), $e->getMessage()));
        }
    }
}
