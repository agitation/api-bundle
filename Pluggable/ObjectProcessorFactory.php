<?php

/*
 * @package    agitation/api-bundle
 * @link       http://github.com/agitation/api-bundle
 * @author     Alexander Günsche
 * @license    http://opensource.org/licenses/MIT
 */

namespace Agit\ApiBundle\Pluggable;

use Agit\BaseBundle\Pluggable\PluggableServiceInterface;

class ObjectProcessorFactory extends AbstractApiProcessorFactory
{
    public function createPluggableService(array $attributes)
    {
        return new ObjectService($attributes);
    }

    public function createProcessor(PluggableServiceInterface $pluggableService)
    {
        return new ObjectProcessor($this->annotationReader, $this->cacheProvider, $pluggableService);
    }
}
