<?php
/**
 * @package    agitation/api
 * @link       http://github.com/agitation/AgitApiBundle
 * @author     Alex Günsche <http://www.agitsol.com/>
 * @copyright  2012-2015 AGITsol GmbH
 * @license    http://opensource.org/licenses/MIT
 */

namespace Agit\ApiBundle\Service;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Agit\PluggableBundle\Strategy\Cache\CacheLoaderFactory;
use Agit\ApiBundle\Common\AbstractEndpointClass;
use Agit\ApiBundle\Exception\IncompatibleFormatterException;
use Agit\PluggableBundle\Strategy\ServiceInjectorTrait;

class FormatterService
{
    use ServiceInjectorTrait;
    use MetaAwareTrait;

    /**
     * @var service container instance.
     */
    protected $container;

    /**
     * @var CacheLoader instance.
     */
    protected $cacheLoader;

    private $formats;

    public function __construct(CacheLoaderFactory $cacheLoaderFactory, ContainerInterface $container)
    {
        $this->cacheLoader = $cacheLoaderFactory->create("agit.api.formatter");
        $this->container = $container;
    }

    public function formatExists($format)
    {
        $this->loadFormats();
        return (is_array($this->formats) && isset($this->formats[$format]));
    }

    public function getFormatter($format, AbstractEndpointClass $endpointClass, Request $request)
    {
        if (!$this->formatExists($format))
            throw new IncompatibleFormatterException("Unknown data format.");

        $class = $this->formats[$format]['class'];
        $meta = $this->formats[$format]['meta'];

        $metaContainer = $this->createMetaContainer(["Formatter" => $meta]);
        $formatter = new $class($metaContainer, $endpointClass, $request);

        $this->injectServices($formatter, $metaContainer->get('Formatter')->get('depends'));

        return $formatter;
    }

    private function loadFormats()
    {
        if (is_null($this->formats))
            $this->formats = $this->cacheLoader->load();
    }
}
