<?php
/**
 * @package    agitation/api
 * @link       http://github.com/agitation/AgitApiBundle
 * @author     Alex Günsche <http://www.agitsol.com/>
 * @copyright  2012-2015 AGITsol GmbH
 * @license    http://opensource.org/licenses/MIT
 */

namespace Agit\ApiBundle\Annotation\Endpoint;

/**
 * @Annotation
 */
class Endpoint extends AbstractEndpointMeta
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
     * @var service dependencies
     */
    public $depends = [];
}
