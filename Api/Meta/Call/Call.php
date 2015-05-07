<?php
/**
 * @package    agitation/api
 * @link       http://github.com/agitation/AgitApiBundle
 * @author     Alex Günsche <http://www.agitsol.com/>
 * @copyright  2012-2015 AGITsol GmbH
 * @license    http://opensource.org/licenses/MIT
 */

namespace Agit\ApiBundle\Api\Meta\Call;

use Agit\ApiBundle\Api\Meta\AbstractMeta;

/**
 * @Annotation
 */
class Call extends AbstractMeta
{
    /**
     * @var root request object namespace/name
     */
    protected $request;

    /**
     * @var root response object namespace/name
     */
    protected $response;

    /**
     * @var if the result is an array of objects, the object type can be passed for automatic filling
     */
    protected $listobject;

    /**
     * @var can be set to `true` if an endpoint provided by a parent class should be skipped.
     */
    protected $inactive;

    /**
     * @var the API namespace. Set by the EndpointService.
     */
    protected $namespace;

    /**
     * @var the API endpoint. Set by the EndpointService.
     */
    protected $endpoint;

    /**
     * @var the method to call.
     */
    protected $method;

    public function setReference($namespace, $endpoint, $method)
    {
        $this->namespace = $namespace;
        $this->endpoint = $endpoint;
        $this->method = $method;
    }
}