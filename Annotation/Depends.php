<?php

/*
 * @package    agitation/api-bundle
 * @link       http://github.com/agitation/api-bundle
 * @author     Alexander Günsche
 * @license    http://opensource.org/licenses/MIT
 */

namespace Agit\ApiBundle\Annotation;

/**
 * @Annotation
 */
class Depends extends Annotation
{
    // a list of services on which a class depends
    public $value = [];
}
