<?php

/*
 * @package    agitation/api-bundle
 * @link       http://github.com/agitation/api-bundle
 * @author     Alexander Günsche
 * @license    http://opensource.org/licenses/MIT
 */

namespace Agit\ApiBundle\Annotation\Endpoint;

use Agit\BaseBundle\Pluggable\PluginInterface;

/**
 * @Annotation
 */
class Controller extends AbstractEndpointMeta implements PluginInterface
{
    /**
     * @var service dependencies
     */
    public $depends = [];
}
