<?php
/**
 * @package    agitation/api
 * @link       http://github.com/agitation/AgitApiBundle
 * @author     Alex Günsche <http://www.agitsol.com/>
 * @copyright  2012-2015 AGITsol GmbH
 * @license    http://opensource.org/licenses/MIT
 */

namespace Agit\ApiBundle\Annotation\Object;

use Agit\CommonBundle\Annotation\SerializableAnnotationInterface;
use Agit\CommonBundle\Annotation\SerializableAnnotationTrait;
use Agit\PluggableBundle\Strategy\PluginInterface;

/**
 * @Annotation
 */
abstract class AbstractObjectMeta implements SerializableAnnotationInterface, PluginInterface
{
    use SerializableAnnotationTrait;
}
