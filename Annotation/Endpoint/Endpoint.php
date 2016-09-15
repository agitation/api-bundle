<?php

/*
 * @package    agitation/api-bundle
 * @link       http://github.com/agitation/api-bundle
 * @author     Alexander Günsche
 * @license    http://opensource.org/licenses/MIT
 */

namespace Agit\ApiBundle\Annotation\Endpoint;

use Agit\ApiBundle\Annotation\Annotation;

/**
 * @Annotation
 */
class Endpoint extends Annotation
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
