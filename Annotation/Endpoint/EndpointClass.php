<?php
/**
 * @package    agitation/api
 * @link       http://github.com/agitation/AgitApiBundle
 * @author     Alex Günsche <http://www.agitsol.com/>
 * @copyright  2012-2015 AGITsol GmbH
 * @license    http://opensource.org/licenses/MIT
 */

namespace Agit\ApiBundle\Annotation\Endpoint;

use Agit\CommonBundle\Annotation\SerializableAnnotationInterface;
use Agit\CommonBundle\Annotation\SerializableAnnotationTrait;

/**
 * @Annotation
 */
class EndpointClass implements SerializableAnnotationInterface
{
    use SerializableAnnotationTrait;

    /**
     * @var the API namespace, such as `common.v1`
     */
    protected $namespace;

    /**
     * @var use calls from a parent endpoint
     *
     * By default, only calls from an endpoint class itself will be registered.
     * But if a parent class provides additional calls, they may be inherited.
     */
    protected $inherits = [];
}
