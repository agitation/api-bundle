<?php
/**
 * @package    agitation/api
 * @link       http://github.com/agitation/AgitApiBundle
 * @author     Alex Günsche <http://www.agitsol.com/>
 * @copyright  2012-2015 AGITsol GmbH
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
