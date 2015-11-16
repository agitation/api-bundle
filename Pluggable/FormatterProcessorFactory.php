<?php
/**
 * @package    agitation/api
 * @link       http://github.com/agitation/AgitApiBundle
 * @author     Alex Günsche <http://www.agitsol.com/>
 * @copyright  2012-2015 AGITsol GmbH
 * @license    http://opensource.org/licenses/MIT
 */

namespace Agit\ApiBundle\Pluggable;

use Agit\PluggableBundle\Strategy\PluggableServiceInterface;

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
