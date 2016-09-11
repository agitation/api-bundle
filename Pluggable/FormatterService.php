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
