<?php

/*
 * @package    agitation/api-bundle
 * @link       http://github.com/agitation/api-bundle
 * @author     Alexander Günsche
 * @license    http://opensource.org/licenses/MIT
 */

/**
 * @link       http://github.com/agitation/AgitApiBundle
 *
 * @author     Alex Günsche <http://www.agitsol.com/>
 * @copyright  2012-2015 AGITsol GmbH
 * @license    http://opensource.org/licenses/MIT
 */

namespace Agit\ApiBundle\Annotation\Endpoint;

/**
 * @Annotation
 */
class Security extends AbstractEndpointMeta
{
    /**
     * @var user capability required for this call.
     */
    protected $capability;

    /**
     * @var whether or not to allow cross-origin requests without a CSRF token.
     */
    protected $allowCrossOrigin = false;
}
