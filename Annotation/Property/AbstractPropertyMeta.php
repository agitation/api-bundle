<?php

/*
 * @package    agitation/api-bundle
 * @link       http://github.com/agitation/api-bundle
 * @author     Alexander Günsche
 * @license    http://opensource.org/licenses/MIT
 */

namespace Agit\ApiBundle\Annotation\Property;

use Agit\BaseBundle\Annotation\SerializableAnnotationInterface;
use Agit\BaseBundle\Annotation\SerializableAnnotationTrait;

abstract class AbstractPropertyMeta implements SerializableAnnotationInterface
{
    use SerializableAnnotationTrait;
}
