<?php
/**
 * @package    agitation/api
 * @link       http://github.com/agitation/AgitApiBundle
 * @author     Alex Günsche <http://www.agitsol.com/>
 * @copyright  2012-2015 AGITsol GmbH
 * @license    http://opensource.org/licenses/MIT
 */

namespace Agit\ApiBundle\Annotation\Object;

use Agit\PluggableBundle\Strategy\PluginInterface;

/**
 * @Annotation
 */
class Object extends AbstractObjectMeta implements PluginInterface
{
    /**
     * @var full object name with namespace prefix, e.g. `common.v1/SomeObject`. Do not set, will be filled automatically.
     */
    protected $objectName;

    /**
     * @var this is a scalar "object", i.e. a dummy object that carries a scalar value.
     */
    protected $isScalar = false;
}
