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
use Doctrine\ORM\EntityManager;
use Agit\BaseBundle\Tool\StringHelper;
use Agit\BaseBundle\Exception\InternalErrorException;
use Agit\BaseBundle\Pluggable\ProcessorInterface;
use Agit\BaseBundle\Pluggable\PluggableServiceInterface;
use Agit\BaseBundle\Pluggable\PluginInterface;
use Agit\BaseBundle\Pluggable\Depends;
use Agit\ApiBundle\Annotation\Endpoint\AbstractEndpointMeta;
use Agit\ApiBundle\Annotation\Endpoint\EntityController;
use Agit\ApiBundle\Annotation\Endpoint\Security;
use Agit\ApiBundle\Annotation\Endpoint\EntityEndpoint;

class EndpointProcessor extends AbstractApiProcessor implements ProcessorInterface
{
    private $cacheProvider;

    private $annotationReader;

    private $entityManager;

    private $entryList = [];

    public function __construct(Reader $annotationReader, CacheProvider $cacheProvider, EntityManager $entityManager, PluggableServiceInterface $pluggableService)
    {
        if (!($pluggableService instanceof EndpointService))
            throw new InternalErrorException("Pluggable service must be an instance of EndpointService.");

        $this->cacheProvider = $cacheProvider;
        $this->entityManager = $entityManager;
        $this->annotationReader = $annotationReader;
    }

    public function addPlugin($class, PluginInterface $plugin)
    {
        $classRefl = new \ReflectionClass($class);
        $controller = $this->translateName($classRefl);
        $namespace = strstr($controller, "/", true);

        // if this is an entity endpoint, there may be inherited endpoints
        if ($plugin instanceof EntityController)
            $this->processEntityController($plugin, $class, $controller);

        foreach ($classRefl->getMethods() as $methodRefl)
        {
            $annotationList = $this->annotationReader->getMethodAnnotations($methodRefl);
            $endpointMeta = [];
            $depends = $plugin->get("depends");

            foreach ($annotationList as $annotation)
            {
                if ($annotation instanceof Depends)
                    $depends = array_merge($depends, (array)$annotation->get("value"));

                if (!($annotation instanceof AbstractEndpointMeta))
                    continue;

                $endpointMetaName = StringHelper::getBareClassName($annotation);
                $endpointMeta[$endpointMetaName] = $annotation;
            }

            if (!isset($endpointMeta["Endpoint"]) || !isset($endpointMeta["Security"]))
                continue;

            $depends = array_merge($depends, (array)$endpointMeta["Endpoint"]->get("depends"));
            $endpointMeta["Endpoint"]->set("depends", $depends);

            // fix implicit namespaces in request and response
            $endpointMeta["Endpoint"]->set("request", $this->fixObjectName($namespace, $endpointMeta["Endpoint"]->get("request")));
            $endpointMeta["Endpoint"]->set("response", $this->fixObjectName($namespace, $endpointMeta["Endpoint"]->get("response")));

            $endpoint = sprintf(
                "%s.%s",
                $controller,
                $methodRefl->getName());

            $this->addEntry($endpoint, [
                "class" => $class,
                "method" => $methodRefl->getName(),
                "meta" => $this->dissectMetaList($endpointMeta)
            ]);
        }
    }

    public function process()
    {
        $this->cacheProvider->save("agit.api.endpoint", $this->getEntries());
    }

    protected function processEntityController($plugin, $class, $controller)
    {
        $capPrefix = $plugin->get("cap");
        $entityMeta = $this->entityManager->getClassMetadata($plugin->get("entity"));
        $entityIdFieldMeta = $entityMeta->getFieldMapping($entityMeta->getSingleIdentifierFieldName());
        $idObject = ($entityIdFieldMeta["type"] === "integer") ? "common.v1/Integer" : "common.v1/String";
        $crossOrigin = $plugin->get("crossOrigin");

        foreach ($plugin->get("endpoints") as $method)
        {
            if (!in_array($method, ["search", "get", "create", "update", "delete", "undelete", "remove"]))
                continue;

            $endpointMeta = [];
            $endpointMeta["Security"] = new Security();
            $endpointMeta["Endpoint"] = new EntityEndpoint([
                "depends" => $plugin->get("depends"),
                "entity" => $plugin->get("entity")
            ]);

            $readCap = $capPrefix ? "$capPrefix.read" : "";
            $writeCap = $capPrefix ? "$capPrefix.write" : "";

            if ($method === "get")
            {
                $endpointMeta["Security"]->set("capability", $readCap);
                $endpointMeta["Endpoint"]->set("request", $idObject);
                $endpointMeta["Endpoint"]->set("response", $controller);
            }
            elseif ($method === "search")
            {
                $endpointMeta["Security"]->set("capability", $readCap);
                $endpointMeta["Endpoint"]->set("request",  "{$controller}Search");
                $endpointMeta["Endpoint"]->set("response", "{$controller}[]");
            }
            elseif (in_array($method, ["create", "update"]))
            {
                $endpointMeta["Security"]->set("capability", $writeCap);
                $endpointMeta["Endpoint"]->set("request", $controller);
                $endpointMeta["Endpoint"]->set("response", $controller);
            }
            elseif (in_array($method, ["delete", "undelete", "remove"]))
            {
                $endpointMeta["Security"]->set("capability", $writeCap);
                $endpointMeta["Endpoint"]->set("request", $idObject);
                $endpointMeta["Endpoint"]->set("response", "common.v1/Null");
            }

            if ($crossOrigin && in_array($method, ["get", "search"]))
            {
                $endpointMeta["Security"]->set("allowCrossOrigin", true);
            }

            $this->addEntry("$controller.$method", [
                "class" => $class,
                "method" => $method,
                "meta" => $this->dissectMetaList($endpointMeta)
            ]);
        }
    }
}
