<?php

/*
 * @package    agitation/api-bundle
 * @link       http://github.com/agitation/api-bundle
 * @author     Alexander Günsche
 * @license    http://opensource.org/licenses/MIT
 */

namespace Agit\ApiBundle\Pluggable;

use Agit\BaseBundle\Annotation\AnnotationTrait;
use Agit\BaseBundle\Pluggable\PluggableServiceInterface;

/**
 * @Annotation
 */
class FormatterService implements PluggableServiceInterface
{
    use AnnotationTrait;

    public function getType()
    {
        return "api.formatter";
    }

    public function getTag()
    {
        return "Agit\ApiBundle\Annotation\Formatter\Formatter";
    }
}
