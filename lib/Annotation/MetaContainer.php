<?php

/*
 * @package    agitation/api-bundle
 * @link       http://github.com/agitation/api-bundle
 * @author     Alexander Günsche
 * @license    http://opensource.org/licenses/MIT
 */

namespace Agit\ApiBundle\Annotation;

use Agit\BaseBundle\Exception\InternalErrorException;

class MetaContainer
{
    private $metas = [];

    public function has($name)
    {
        return isset($this->metas[$name]);
    }

    public function set($name, Annotation $meta)
    {
        $this->metas[$name] = $meta;
    }

    public function get($name)
    {
        if (! isset($this->metas[$name])) {
            throw new InternalErrorException("No meta named `$name` found.");
        }

        return $this->metas[$name];
    }
}
