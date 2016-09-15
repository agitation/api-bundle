<?php

/*
 * @package    agitation/api-bundle
 * @link       http://github.com/agitation/api-bundle
 * @author     Alexander Günsche
 * @license    http://opensource.org/licenses/MIT
 */

namespace Agit\ApiBundle\Api\Controller;

use Agit\ApiBundle\Annotation\Depends;
use Agit\ApiBundle\Api\Object\AbstractEntityObject;
use Agit\ApiBundle\Api\Object\RequestObjectInterface;
use Agit\ApiBundle\Exception\BadRequestException;
use Agit\ApiBundle\Exception\ObjectNotFoundException;
use Agit\ApiBundle\Service\PersistenceService;
use Agit\BaseBundle\Entity\DeletableInterface;
use Agit\BaseBundle\Exception\InternalErrorException;
use Agit\BaseBundle\Tool\StringHelper;
use Agit\IntlBundle\Tool\Translate;
use Agit\LoggingBundle\Service\Logger;
use Agit\MultilangBundle\Multilang;
use Doctrine\ORM\EntityManager;
use Exception;
use Psr\Log\LogLevel;

/**
 * @Depends({"@doctrine.orm.entity_manager", "@agit.api.persistence"});
 *
 * Endpoint class providing CRUD operations for entities.
 *
 * NOTE: The `get`, `create`, `update`, `delete`, `undelete`, `remove` and `search`
 * methods can be used as endpoints – even though they don’t have annotations
 * on them.
 *
 * The actual endpoint class can tell through the EntityController annotation
 * which of these methods it wants to provide.
 *
 * It is also possible to override them, there are two different ways: The first
 * is to simply declare a method with the respective name, without annotations.
 * In this case, the method will assume the standard request/response/capability
 * settings. It is also possible to annotate the class with a custom configuration.
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

    public function get($id)
    {
        $this->checkPermissions($id, __FUNCTION__);
        $entity = $this->retrieveEntity($this->getEntityClass(), $id);

        return $this->createObject($this->getResponseObjectApiClass(), $entity);
    }

    public function create(AbstractEntityObject $requestObject)
    {
        $this->checkPermissions($requestObject, __FUNCTION__);
        $this->validate($requestObject);

        try {
            $this->entityManager->beginTransaction();

            $className = $this->entityManager->getClassMetadata($this->getEntityClass())->getName();
            $entity = $this->saveEntity(new $className(), $requestObject);

            $this->logger->log(
                LogLevel::WARNING,
                "agit.api.entity",
                sprintf(Translate::tl("New object %s of type %s has been created."), $entity->getId(), $this->getEntityClassName($entity)),
                true
            );

            $this->entityManager->commit();
        } catch (Exception $e) {
            $this->entityManager->rollBack();
            throw $e;
        }

        return $this->createObject($this->getResponseObjectApiClass(), $entity);
    }

    public function update(AbstractEntityObject $requestObject)
    {
        $this->checkPermissions($requestObject, __FUNCTION__);
        $this->validate($requestObject);

        try {
            $this->entityManager->beginTransaction();

            $entity = $this->retrieveEntity($this->getEntityClass(), $requestObject->get("id"));
            $entity = $this->saveEntity($entity, $requestObject);

            $this->logger->log(
                LogLevel::WARNING,
                "agit.api.entity",
                sprintf(Translate::tl("Object “%s” of type “%s” has been updated."), $this->getEntityName($entity), $this->getEntityClassName($entity)),
                true
            );

            $this->entityManager->commit();
        } catch (Exception $e) {
            $this->entityManager->rollBack();
            throw $e;
        }

        return $this->createObject($this->getResponseObjectApiClass(), $entity);
    }

    public function delete($id)
    {
        $this->checkPermissions($id, __FUNCTION__);

        try {
            $this->entityManager->beginTransaction();

            $entity = $this->retrieveEntity($this->getEntityClass(), $id);

            if (! ($entity instanceof DeletableInterface)) {
                throw new InternalErrorException("Only entities which implement the DeletableInterface can be deleted here.");
            }

            if ($entity->isDeleted()) {
                throw new BadRequestException(Translate::t("This entity is already deleted."));
            }

            $entity->setDeleted(true);
            $this->entityManager->persist($entity);

            $this->entityManager->flush();

            $this->logger->log(
                LogLevel::WARNING,
                "agit.api.entity",
                sprintf(Translate::tl("Object %s of type %s has been deleted."), $entity->getId(), $this->getEntityClassName($entity)),
                true
            );

            $this->entityManager->commit();
        } catch (Exception $e) {
            $this->entityManager->rollBack();
            throw $e;
        }

        return true;
    }

    public function undelete($id)
    {
        $this->checkPermissions($id, __FUNCTION__);

        try {
            $this->entityManager->beginTransaction();

            $entity = $this->retrieveEntity($this->getEntityClass(), $id);

            if (! ($entity instanceof DeletableInterface)) {
                throw new InternalErrorException("Only entities which implement the DeletableInterface can be undeleted here.");
            }

            if (! $entity->isDeleted()) {
                throw new BadRequestException(Translate::t("This entity is not deleted and hence cannot be undeleted."));
            }

            $entity->setDeleted(false);
            $this->entityManager->persist($entity);
            $this->entityManager->flush();

            $this->logger->log(
                LogLevel::WARNING,
                "agit.api.entity",
                sprintf(Translate::tl("Object %s of type %s has been undeleted."), $entity->getId(), $this->getEntityClassName($entity)),
                true
            );

            $this->entityManager->commit();
        } catch (Exception $e) {
            $this->entityManager->rollBack();
            throw $e;
        }

        return true;
    }

    public function remove($id)
    {
        $this->checkPermissions($id, __FUNCTION__);

        $entity = $this->retrieveEntity($this->getEntityClass(), $id);

        try {
            $this->entityManager->beginTransaction();

            $this->entityManager->remove($entity);
            $this->entityManager->flush();

            $this->logger->log(
                LogLevel::WARNING,
                "agit.api.entity",
                sprintf(Translate::tl("Object %s of type %s has been removed permanently."), $entity->getId(), $this->getEntityClassName($entity)),
                true
            );

            $this->entityManager->commit();
        } catch (Exception $e) {
            $this->entityManager->rollBack();
            throw new BadRequestException(Translate::t("This object cannot be removed, because there are other objects depending on it."));
        }
    }

    public function search(RequestObjectInterface $requestObject)
    {
        $this->checkPermissions($requestObject, __FUNCTION__);

        $this->responseService->setView("search");

        $query = $this->createSearchQuery($requestObject);
        $result = [];

        foreach ($query->getQuery()->getResult() as $entity) {
            $result[] = $this->createObject($this->getResponseObjectApiClass(), $entity);
        }

        return $result;
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
        $this->getService("agit.api.persistence")->saveEntity(
            $entity,
            $this->getPersistableData($request)
        );

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
            $output = new \stdClass();
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
