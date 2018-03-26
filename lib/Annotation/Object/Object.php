<?php
declare(strict_types=1);

/*
 * @package    agitation/api-bundle
 * @link       http://github.com/agitation/api-bundle
 * @author     Alexander Günsche
 * @license    http://opensource.org/licenses/MIT
 */

namespace Agit\ApiBundle\Annotation\Object;

/**
 * @Annotation
 */
class Object extends AbstractObjectMeta
{
    /**
     * @var string the API namespace, e.g. `foobar.v1`
     */
    public $namespace;

    /**
     * @var string full object name with namespace prefix, e.g. `foobar.v1/SomeObject`.
     *
     * @internal do not set this field, it will be filled automatically
     */
    protected $objectName;
}
