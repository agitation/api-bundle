<?php

/*
 * @package    agitation/api-bundle
 * @link       http://github.com/agitation/api-bundle
 * @author     Alexander Günsche
 * @license    http://opensource.org/licenses/MIT
 */

namespace Agit\ApiBundle\Annotation\Object;

use Agit\BaseBundle\Pluggable\PluginInterface;

/**
 * @Annotation
 */
class Object extends AbstractObjectMeta implements PluginInterface
{
    /**
     * @var service dependencies
     */
    public $depends = [];

    /**
     * @var this is a super object and cannot be instantiated as such
     */
    protected $super = false;

    /**
     * @var this is a scalar "object", i.e. a dummy object that carries a scalar value.
     */
    protected $scalar = false;

    /**
     * @var full object name with namespace prefix, e.g. `common.v1/SomeObject`.
     *
     * @internal Do not set this field, it will be filled automatically.
     */
    protected $objectName;

    /**
     * @var if the parent is a super class, this field contains its name.
     *
     * @internal Do not set this field, it will be filled automatically.
     */
    protected $parentObjectName;
}
