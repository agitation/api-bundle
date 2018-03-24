<?php
declare(strict_types=1);

/*
 * @package    agitation/api-bundle
 * @link       http://github.com/agitation/api-bundle
 * @author     Alexander Günsche
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
}
