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
class Security extends Annotation
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
