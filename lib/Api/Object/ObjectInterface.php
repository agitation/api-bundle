<?php
declare(strict_types=1);
/*
 * @package    agitation/api-bundle
 * @link       http://github.com/agitation/api-bundle
 * @author     Alexander Günsche
 * @license    http://opensource.org/licenses/MIT
 */

namespace Agit\ApiBundle\Api\Object;

use Agit\ApiBundle\Annotation\MetaContainer;
use Agit\ApiBundle\Service\ObjectMetaService;
use Exception;
use JsonSerializable;

interface ObjectInterface extends JsonSerializable
{
    /**
     * initializes the object (__construct is reserved for specific implementation).
     *
     * @param string            $name
     * @param ObjectMetaService $objectMetaService
     */
    public function init($name, ObjectMetaService $objectMetaService);

    /**
     * @return string
     */
    public function getName();

    /**
     * @return string
     */
    public function getObjectName();

    /**
     * @return array
     */
    public function getKeys();

    /**
     * @param string $key
     *
     * @return bool
     */
    public function has($key);

    /**
     * @param string $key
     *
     * @return mixed
     */
    public function get($key);

    /**
     * @return array
     */
    public function getValues();

    /**
     * @param string $key
     * @param mixed  $value
     */
    public function set($key, $value);

    /**
     * @param string $key
     * @param mixed  $value
     */
    public function add($key, $value);

    /**
     * Returns a specific meta field for a property.
     *
     * @param string $prop     property name
     * @param string $metaName name of the meta field
     * @param mixed  $propKey
     *
     * @return MetaContainer
     */
    public function getPropertyMeta($propKey, $metaName);

    /**
     * Checks if all fields have a valid value. ATTENTION: When overriding this method,
     * make sure to call it in the child.
     *
     * @throws Exception
     */
    public function validate();
}
