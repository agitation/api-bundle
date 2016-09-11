<?php

/*
 * @package    agitation/api-bundle
 * @link       http://github.com/agitation/api-bundle
 * @author     Alexander Günsche
 * @license    http://opensource.org/licenses/MIT
 */

namespace Agit\ApiBundle\Annotation;

use Agit\BaseBundle\Annotation\SerializableAnnotationInterface;
use Agit\BaseBundle\Exception\InternalErrorException;

class MetaContainer
{
    private $metaList = [];

    public function has($name)
    {
        return isset($this->metaList[$name]);
    }

    public function set($name, SerializableAnnotationInterface $meta)
    {
        $this->metaList[$name] = $meta;
    }

    public function get($name)
    {
        if (! isset($this->metaList[$name])) {
            throw new InternalErrorException("No meta named `$name` found.");
        }

        return $this->metaList[$name];
    }
}
