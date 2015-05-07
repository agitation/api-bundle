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
use Agit\CoreBundle\Pluggable\Strategy\Cache\CacheLoader;
use Agit\ApiBundle\Api\Endpoint\AbstractEndpoint;
use Agit\ApiBundle\Exception\IncompatibleFormatterException;

class FormatterService
{
    /**
     * @var service container instance.
     */
    protected $Container;

    /**
     * @var CacheLoader instance.
     */
    protected $CacheLoader;

    private $formats;

    public function __construct(CacheLoader $CacheLoader, ContainerInterface $Container)
    {
        $this->CacheLoader = $CacheLoader;
        $this->Container = $Container;
    }

    public function formatExists($format)
    {
        if (is_null($this->formats))
            $this->formats = $this->CacheLoader->loadPlugins();

        return isset($this->formats[$format]);
    }

    public function getFormatter($format, AbstractEndpoint $Endpoint, Request $Request)
    {
        if (!$this->formatExists($format))
            throw new IncompatibleFormatterException("Unknown data format.");

        $formatterClassName = $this->formats[$format];
        $Formatter = new $formatterClassName($this->Container, $Endpoint, $Request);

        return $Formatter;
    }
}
