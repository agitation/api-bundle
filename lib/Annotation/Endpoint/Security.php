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
class Security extends AbstractEndpointMeta
{
    /**
     * @var user capability required for this call
     */
    protected $capability;
}
