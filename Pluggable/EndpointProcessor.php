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
use Agit\CommonBundle\Helper\StringHelper;
use Agit\CommonBundle\Exception\InternalErrorException;
use Agit\PluggableBundle\Strategy\ProcessorInterface;
use Agit\PluggableBundle\Strategy\PluggableServiceInterface;
use Agit\PluggableBundle\Strategy\PluginInterface;
use Agit\ApiBundle\Annotation\Endpoint\AbstractEndpointMeta;
use Agit\ApiBundle\Annotation\Endpoint\Security;

class EndpointProcessor extends AbstractApiProcessor implements ProcessorInterface
{
    private $cacheProvider;

    private $annotationReader;

    private $entryList = [];

    public function __construct(Reader $annotationReader, CacheProvider $cacheProvider, PluggableServiceInterface $pluggableService)
    {
        if (!($pluggableService instanceof EndpointService))
            throw new InternalErrorException("Pluggable service must be an instance of EndpointService.");

        $this->cacheProvider = $cacheProvider;
        $this->annotationReader = $annotationReader;
    }

    public function addPlugin($class, PluginInterface $plugin)
    {
        $classRefl = new \ReflectionClass($class);
        $className = $this->translateName($classRefl);
        $namespace = strstr($className, '/', true);

        foreach ($classRefl->getMethods() as $methodRefl)
        {
            $annotationList = $this->annotationReader->getMethodAnnotations($methodRefl);
            $endpointMeta = [];

            foreach ($annotationList as $annotation)
            {
                if (!($annotation instanceof AbstractEndpointMeta))
                    continue;

                $endpointMetaName = StringHelper::getBareClassName($annotation);
                $endpointMeta[$endpointMetaName] = $annotation;
            }

            if (!isset($endpointMeta['Endpoint']) || !isset($endpointMeta['Security']))
                continue;

            // fix implicit namespaces in request and response
            $endpointMeta['Endpoint']->set('request', $this->fixObjectName($namespace, $endpointMeta['Endpoint']->get('request')));
            $endpointMeta['Endpoint']->set('response', $this->fixObjectName($namespace, $endpointMeta['Endpoint']->get('response')));

            $endpoint = sprintf(
                "%s.%s",
                $className,
                $methodRefl->getName());

            $this->addEntry($endpoint, [
                'class' => $class,
                'meta' => $this->dissectMetaList($endpointMeta)
            ]);
        }
    }

    public function process()
    {
        $this->cacheProvider->save("agit.api.endpoint", $this->getEntries());
    }
}
