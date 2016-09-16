<?php

/*
 * @package    agitation/api-bundle
 * @link       http://github.com/agitation/api-bundle
 * @author     Alexander Günsche
 * @license    http://opensource.org/licenses/MIT
 */

namespace Agit\ApiBundle\Api\Controller;

use Agit\ApiBundle\Api\Object\AbstractEntityObject;
use Agit\ApiBundle\Api\Object\RequestObjectInterface;
use Agit\ApiBundle\Exception\ObjectNotFoundException;
use Agit\ApiBundle\Service\PersistenceService;
use Agit\BaseBundle\Tool\StringHelper;
use Agit\IntlBundle\Tool\Translate;
use Agit\LoggingBundle\Service\Logger;
use Agit\MultilangBundle\Multilang;
use Doctrine\ORM\EntityManager;
use stdClass;

/**
 * Endpoint class providing CRUD operations for entities.
 *
 * NOTE: The `get`, `create`, `update`, `delete`, `undelete`, `remove` and `search`
 * methods must be imported through the Entity*Trait traits. They don’t have
 * explicit annotations; these are generated on the fly by the ControllerProcessor.
 *
 * It possible to override the trait methods, while keeping the definitions.
 * Simply declare a method with the same name, without annotations.
 */
abstract class AbstractEntityController extends AbstractController
{
    private $entityManager;

    private $logger;

    private $persistenceService;

    public function initExtra(PersistenceService $persistenceService, EntityManager $entityManager, Logger $logger)
    {
        $this->persistenceService = $persistenceService;
        $this->entityManager = $entityManager;
        $this->logger = $logger;
    }

    /**
     * Additional security checks, e.g. for entity endpoints which are not based
     * on the current user’s capabilities. To be overridden in the entity
     * controller, if necessary.
     */
    protected function checkPermissions($request, $type)
    {
    }

    /**
     * Optional extra validation for create/update (e.g. consistency checks),
     * may be overridden in the entity controller, if necessary.
     */
    protected function validate(AbstractEntityObject $requestObject)
    {
    }

    protected function getEntityClass()
    {
        return $this->getMeta("Endpoint")->get("entity");
    }

    protected function getResponseObjectApiClass()
    {
        $apiClass = $this->getMeta("Endpoint")->get("response");

        if (substr($apiClass, -2) === "[]") {
            $apiClass = substr($apiClass, 0, -2);
        }

        return $apiClass;
    }

    protected function createSearchQuery(RequestObjectInterface $requestObject)
    {
        $entityName = $this->getEntityClass();

        $query =  $this->entityManager
            ->createQueryBuilder()
            ->select("e")->from($entityName, "e")
            ->orderBy("e.id", "ASC");

        return $query;
    }

    protected function retrieveEntity($entityClass, $id)
    {
        $entity = $this->entityManager
            ->getRepository($entityClass)
            ->findOneBy(["id" => $id]);

        if (! $entity) {
            throw new ObjectNotFoundException(sprintf(Translate::t("The requested object with ID `%s` was not found."), $id));
        }

        return $entity;
    }

    protected function saveEntity($entity, $request)
    {
        $this->persistenceService->saveEntity($entity, $this->getPersistableData($request));

        return $entity;
    }

    protected function getEntityName($entity)
    {
        return is_callable([$entity, "getName"])
            ? Multilang::u($entity->getName())
            : $entity->getId();
    }

    protected function getEntityClassName($entity)
    {
        return is_callable([$entity, "getEntityClassName"])
            ? $entity->getEntityClassName()
            : StringHelper::getBareClassName($entity);
    }

    protected function getPersistableData($input)
    {
        $output = null;

        if (is_object($input)) {
            $output = new stdClass();
            $reqObj = null;

            if ($input instanceof AbstractEntityObject || $input instanceof AbstractValueObject || $input instanceof AbstractRequestObject) {
                $reqObj = $input;
                $input = $input->getValues();
            }

            foreach ($input as $key => $value) {
                if (! $reqObj || ! $reqObj->getPropertyMeta($key, "Type")->get("readonly")) {
                    $output->$key = $this->getPersistableData($value);
                }
            }
        } elseif (is_array($input)) {
            $output = [];

            foreach ($input as $key => $value) {
                $output[$key] = $this->getPersistableData($value);
            }
        } else {
            $output = $input;
        }

        return $output;
    }
}
