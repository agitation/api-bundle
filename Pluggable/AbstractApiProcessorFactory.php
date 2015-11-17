<?php
/**
 * @package    agitation/api
 * @link       http://github.com/agitation/AgitApiBundle
 * @author     Alex Günsche <http://www.agitsol.com/>
 * @copyright  2012-2015 AGITsol GmbH
 * @license    http://opensource.org/licenses/MIT
 */

namespace Agit\ApiBundle\Pluggable;

use Doctrine\Common\Cache\CacheProvider;
use Doctrine\Common\Annotations\Reader;
use Agit\PluggableBundle\Strategy\ProcessorFactoryInterface;

abstract class AbstractApiProcessorFactory implements ProcessorFactoryInterface
{
    protected $cacheProvider;

    protected $annotationReader;

    public function __construct(Reader $annotationReader, CacheProvider $cacheProvider)
    {
        $this->annotationReader = $annotationReader;
        $this->cacheProvider = $cacheProvider;
    }
}