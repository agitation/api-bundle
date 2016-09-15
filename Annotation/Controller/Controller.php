<?php

/*
 * @package    agitation/api-bundle
 * @link       http://github.com/agitation/api-bundle
 * @author     Alexander Günsche
 * @license    http://opensource.org/licenses/MIT
 */

namespace Agit\ApiBundle\Annotation\Controller;

use Agit\ApiBundle\Annotation\Annotation;

/**
 * @Annotation
 */
class Controller extends Annotation
{
    /**
     * @var the API namespace, e.g. `foobar.v1`
     */
    public $namespace;
}
