<?php

/*
 * @package    agitation/api-bundle
 * @link       http://github.com/agitation/api-bundle
 * @author     Alexander Günsche
 * @license    http://opensource.org/licenses/MIT
 */

namespace Agit\ApiBundle\Service;

use Agit\ApiBundle\Annotation\Annotation;
use Agit\ApiBundle\Annotation\Controller\Controller;
use Agit\ApiBundle\Annotation\Controller\EntityController;
use Agit\ApiBundle\Annotation\Depends;
use Agit\ApiBundle\Annotation\Endpoint\AbstractEndpointMeta;
use Agit\ApiBundle\Annotation\Endpoint\EntityEndpoint;
use Agit\ApiBundle\Annotation\Endpoint\Security;
use Agit\BaseBundle\Exception\InternalErrorException;
use Agit\BaseBundle\Tool\StringHelper;
use Doctrine\Common\Annotations\Reader;
use Doctrine\Common\Cache\Cache;
use Doctrine\ORM\EntityManager;
use ReflectionClass;
use Symfony\Component\HttpKernel\Kernel;

class ControllerProcessor extends AbstractProcessor
{
    const ENTITY_TRAIT_NAMESPACE = "Agit\ApiBundle\Api\Controller";

    const SUPPORTED_ENTITY_TRAITS = [
        "search"   => "EntitySearchTrait",
        "get"      => "EntityGetTrait",
        "create"   => "EntityCreateTrait",
        "update"   => "EntityUpdateTrait",
        "delete"   => "EntityDeleteTrait",
        "undelete" => "EntityUndeleteTrait",
        "remove"   => "EntityRemoveTrait"
    ];

    protected $kernel;

    protected $classCollector;

    protected $cacheProvider;

    protected $annotationReader;

    protected $entityManager;

    public function __construct(Kernel $kernel, ClassCollector $classCollector, Reader $annotationReader, Cache $cacheProvider, EntityManager $entityManager)
    {
        $this->kernel = $kernel;
        $this->classCollector = $classCollector;
        $this->cacheProvider = $cacheProvider;
        $this->annotationReader = $annotationReader;
        $this->entityManager = $entityManager;
    }

    public function process()
    {
        $this->collect(Controller::class, "agit.api.endpoint");
    }

    protected function processClass(ReflectionClass $classRefl, Annotation $desc, array $classAnnotations)
    {
        $class = $classRefl->getName();
        $namespace = $desc->get("namespace");
        $controllerName = "$namespace/" . $classRefl->getShortName();
        $deps = isset($classAnnotations[Depends::class]) ? $classAnnotations[Depends::class] : new Depends();

        $this->checkConstructor($classRefl, $deps);

        if (! $namespace) {
            throw new InternalErrorException(sprintf("ATTENTION: missing namespace on %s\n", $class));
        }

        if ($desc instanceof EntityController) {
            $this->processEntityController($desc, $classRefl, $controllerName, $deps);
        }

        foreach ($classRefl->getMethods() as $methodRefl) {
            $annotationList = $this->annotationReader->getMethodAnnotations($methodRefl);
            $endpointMeta = [];

            foreach ($annotationList as $annotation) {
                if ($annotation instanceof AbstractEndpointMeta) {
                    $endpointMetaName = StringHelper::getBareClassName($annotation);
                    $endpointMeta[$endpointMetaName] = $annotation;
                }
            }

            if (! isset($endpointMeta["Endpoint"]) || ! isset($endpointMeta["Security"])) {
                continue;
            }

            if (! $methodRefl->isPublic()) {
                throw new InternalErrorException(sprintf("ATTENTION: %s->%s must be public!\n", $class, $methodRefl->getName()));
            }

            // fix implicit namespaces in request and response
            $endpointMeta["Endpoint"]->set("request", $this->fixObjectName($namespace, $endpointMeta["Endpoint"]->get("request")));
            $endpointMeta["Endpoint"]->set("response", $this->fixObjectName($namespace, $endpointMeta["Endpoint"]->get("response")));

            $endpoint = sprintf(
                "%s.%s",
                $controllerName,
                $methodRefl->getName());

            $this->addEntry($endpoint, [
                "class"  => $class,
                "deps"   => $this->dissectMeta($deps),
                "method" => $methodRefl->getName(),
                "meta"   => $this->dissectMetaList($endpointMeta)
            ]);
        }
    }

    protected function processEntityController($desc, $classRefl, $controllerName, $deps)
    {
        $class = $classRefl->getName();
        $capPrefix = $desc->get("cap");
        $entityMeta = $this->entityManager->getClassMetadata($desc->get("entity"));
        $entityIdFieldMeta = $entityMeta->getFieldMapping($entityMeta->getSingleIdentifierFieldName());
        $idObject = ($entityIdFieldMeta["type"] === "integer") ? "common.v1/ScalarInteger" : "common.v1/ScalarString";
        $crossOrigin = $desc->get("crossOrigin");

        $usedTraits = $this->getTraits($classRefl);

        foreach ($usedTraits as $method) {
            $endpointMeta = [];
            $endpointMeta["Security"] = new Security();
            $endpointMeta["Endpoint"] = new EntityEndpoint([
                "entity"  => $desc->get("entity")
            ]);

            $readCap = $capPrefix ? "$capPrefix.read" : "";
            $writeCap = $capPrefix ? "$capPrefix.write" : "";

            if ($method === "get") {
                $endpointMeta["Security"]->set("capability", $readCap);
                $endpointMeta["Endpoint"]->set("request", $idObject);
                $endpointMeta["Endpoint"]->set("response", $controllerName);
            } elseif ($method === "all") {
                $endpointMeta["Security"]->set("capability", $readCap);
                $endpointMeta["Endpoint"]->set("request",  "common.v1/ScalarNull");
                $endpointMeta["Endpoint"]->set("response", "{$controllerName}[]");
            } elseif ($method === "search") {
                $endpointMeta["Security"]->set("capability", $readCap);
                $endpointMeta["Endpoint"]->set("request",  "{$controllerName}Search");
                $endpointMeta["Endpoint"]->set("response", "{$controllerName}[]");
            } elseif (in_array($method, ["create", "update"])) {
                $endpointMeta["Security"]->set("capability", $writeCap);
                $endpointMeta["Endpoint"]->set("request", $controllerName);
                $endpointMeta["Endpoint"]->set("response", $controllerName);
            } elseif (in_array($method, ["delete", "undelete", "remove"])) {
                $endpointMeta["Security"]->set("capability", $writeCap);
                $endpointMeta["Endpoint"]->set("request", $idObject);
                $endpointMeta["Endpoint"]->set("response", "common.v1/ScalarNull");
            }

            if ($crossOrigin && in_array($method, ["get", "search"])) {
                $endpointMeta["Security"]->set("allowCrossOrigin", true);
            }

            $this->addEntry("$controllerName.$method", [
                "class"  => $class,
                "deps"   => $this->dissectMeta($deps),
                "method" => $method,
                "meta"   => $this->dissectMetaList($endpointMeta)
            ]);
        }
    }

    private function getTraits(ReflectionClass $classRefl)
    {
        $usedTraits = [];

        foreach ($classRefl->getTraits() as $usedTrait) {
            foreach (self::SUPPORTED_ENTITY_TRAITS as $method => $supportedTrait) {
                $traitClass = self::ENTITY_TRAIT_NAMESPACE . "\\$supportedTrait";

                if ($usedTrait->name === $traitClass || $usedTrait->isSubclassOf($traitClass)) {
                    $usedTraits[] = $method;
                    break;
                }
            }

            // get traits of traits
            $usedTraits = array_merge($usedTraits, $this->getTraits($usedTrait));
        }

        if ($parent = $classRefl->getParentClass()) {
            $usedTraits = array_merge($usedTraits, $this->getTraits($parent)); // get parent traits
        }

        return $usedTraits;
    }
}
