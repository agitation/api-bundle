<?php
/**
 * @package    agitation/api
 * @link       http://github.com/agitation/AgitApiBundle
 * @author     Alex Günsche <http://www.agitsol.com/>
 * @copyright  2012-2015 AGITsol GmbH
 * @license    http://opensource.org/licenses/MIT
 */

namespace Agit\ApiBundle\Annotation\Endpoint;

use Agit\PluggableBundle\Strategy\PluginInterface;

/**
 * @Annotation
 *
 * A special type of endpoint class which is responsible for CRUD operations on a given entity
 */
class EntityController extends Controller
{
    /**
     * @var the entity this class refers to
     */
    public $entity;

    /**
     * @var the endpoints this class offers. One or more of "get", "create", "update", "delete", "search".
     */
    public $endpoints;

    /**
     * @var a prefix for xxx.read and xxx.write capabilities
     *
     * xxx.read is for "get" and "search", xxx.write is for "create", "update", "delete"
     */
    public $cap;

    /**
     * @var boolean if the call should allow cross-origin requests. Only available for get/search requests.
     */
    public $crossOrigin;
}
