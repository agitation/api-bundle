<?php

/*
 * @package    agitation/api-bundle
 * @link       http://github.com/agitation/api-bundle
 * @author     Alexander Günsche
 * @license    http://opensource.org/licenses/MIT
 */

namespace Agit\ApiBundle\Pluggable;

use Agit\BaseBundle\Pluggable\PluggableServiceInterface;

class FormatterProcessorFactory extends AbstractApiProcessorFactory
{
    public function createPluggableService(array $attributes)
    {
        return new FormatterService($attributes);
    }

    public function createProcessor(PluggableServiceInterface $pluggableService)
    {
        return new FormatterProcessor($this->annotationReader, $this->cacheProvider, $pluggableService);
    }
}
