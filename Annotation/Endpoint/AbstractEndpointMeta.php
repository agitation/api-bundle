<?php
/**
 * @package    agitation/api
 * @link       http://github.com/agitation/AgitApiBundle
 * @author     Alex Günsche <http://www.agitsol.com/>
 * @copyright  2012-2015 AGITsol GmbH
 * @license    http://opensource.org/licenses/MIT
 */

namespace Agit\ApiBundle\Annotation\Endpoint;

use Agit\BaseBundle\Annotation\SerializableAnnotationInterface;
use Agit\BaseBundle\Annotation\SerializableAnnotationTrait;
use Agit\BaseBundle\Pluggable\PluginInterface;

/**
 * @Annotation
 */
abstract class AbstractEndpointMeta implements SerializableAnnotationInterface, PluginInterface
{
    use SerializableAnnotationTrait;
}
