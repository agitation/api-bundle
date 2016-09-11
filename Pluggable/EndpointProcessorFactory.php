<?php

/*
 * @package    agitation/api-bundle
 * @link       http://github.com/agitation/api-bundle
 * @author     Alexander Günsche
 * @license    http://opensource.org/licenses/MIT
 */

namespace Agit\ApiBundle\Pluggable;

use Agit\BaseBundle\Pluggable\PluggableServiceInterface;

class EndpointProcessorFactory extends AbstractApiProcessorFactory
{
    public function createPluggableService(array $attributes)
    {
        return new EndpointService($attributes);
    }

    public function createProcessor(PluggableServiceInterface $pluggableService)
    {
        return new EndpointProcessor($this->annotationReader, $this->cacheProvider, $this->entityManager, $pluggableService);
    }
}
