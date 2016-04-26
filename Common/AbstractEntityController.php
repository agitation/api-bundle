<?php
/**
 * @package    agitation/api
 * @link       http://github.com/agitation/AgitApiBundle
 * @author     Alex Günsche <http://www.agitsol.com/>
 * @copyright  2012-2015 AGITsol GmbH
 * @license    http://opensource.org/licenses/MIT
 */

namespace Agit\ApiBundle\Common;

use Agit\CommonBundle\Exception\InternalErrorException;
use Agit\IntlBundle\Translate;
use Agit\PluggableBundle\Strategy\Depends;
use Agit\ApiBundle\Common\RequestObjectInterface;
use Agit\ApiBundle\Common\AbstractEntityObject;
use Agit\ApiBundle\Exception\ObjectNotFoundException;

/**
 * @Depends({"doctrine.orm.entity_manager", "agit.api.persistence"});
 *
 * Endpoint class providing CRUD operations for entities.
 *
 * NOTE: The `get`, `create`, `update`, `delete` and `search` methods can be used
 * as endpoints – even though they don’t have annotations on them.
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
    protected function get($id)
    {
        $entity = $this->retrieveEntity($this->getEntityClass(), $id);
        return $this->createObject($this->getResponseObjectApiClass(), $entity);
    }

    protected function create(AbstractEntityObject $requestObject)
    {
        $this->validate($requestObject);

        $em = $this->getService("doctrine.orm.entity_manager");
        $className = $em->getClassMetadata($this->getEntityClass())->getName();
        $entity = $this->saveEntity(new $className(), $requestObject);

        return $this->createObject($this->getResponseObjectApiClass(), $entity);
    }

    protected function update(AbstractEntityObject $requestObject)
    {
        $this->validate($requestObject);

        $entity = $this->retrieveEntity($this->getEntityClass(), $requestObject->get("id"));
        $entity = $this->saveEntity($entity, $requestObject);

        return $this->createObject($this->getResponseObjectApiClass(), $entity);
    }

    // extra validation for create/update (e.g. consistency checks), may be overloaded in the endpoint class
    protected function validate(AbstractEntityObject $requestObject)
    {
    }

    protected function delete($id, $completeRemove = true)
    {
        try
        {
            $deleted = false;
            $em = $this->getService("doctrine.orm.entity_manager");

            // first, try to just set the status to "deleted" ...
            $em->beginTransaction();

            $entity = $this->retrieveEntity($this->getEntityClass(), $id);

            if (method_exists($entity, "setStatus"))
            {
                $entity->setStatus(-1);
                $em->persist($entity);
                $em->flush();

                $deleted = true;
            }

            $em->commit();

            // ... then try to completely remove the entity
            // (if it fails, the entity has dependencies)

            if ($completeRemove)
            {
                $em->beginTransaction();

                $em->remove($entity);
                $em->flush();

                $em->commit();
                $deleted = true;
            }
        }
        catch(\Exception $e)
        {
            $em->rollback();

            // reload the entity manager after a "failed" full delete
            if (!$em->isOpen())
                $em = $em->create($em->getConnection(), $em->getConfiguration(), $em->getEventManager());

            if (!$deleted)
                throw new InternalErrorException(sprintf("Failed deleting an object of type %s, possibly because of dependencies and lack of the setStatus() method.", $this->getEntityClass()));
        }

        return true;
    }

    protected function search(RequestObjectInterface $requestObject)
    {
        $this->responseService->setView("search");

        $query = $this->createSearchQuery($requestObject);
        $result = [];

        foreach ($query->getQuery()->getResult() as $entity)
            $result[] = $this->createObject($this->getResponseObjectApiClass(), $entity);

        return $result;
    }

    protected function getEntityClass()
    {
        return $this->getMeta("Endpoint")->get("entity");
    }

    protected function getResponseObjectApiClass()
    {
        $apiClass = $this->getMeta("Endpoint")->get("response");

        if (substr($apiClass, -2) === "[]")
            $apiClass = substr($apiClass, 0, -2);

        return $apiClass;
    }

    protected function createSearchQuery(RequestObjectInterface $requestObject)
    {
        $entityName = $this->getEntityClass();

        $query =  $this->getService("doctrine.orm.entity_manager")
            ->createQueryBuilder()
            ->select("e")->from($entityName, "e")
            ->orderBy("e.id", "ASC");

        return $query;
    }

    protected function retrieveEntity($entityClass, $id)
    {
        $entity = $this->getService("doctrine.orm.entity_manager")
            ->getRepository($entityClass)
            ->findOneBy(["id" => $id]);

        if (!$entity)
            throw new ObjectNotFoundException(sprintf(Translate::t("The requested object with ID `%s` was not found."), $id));

        return $entity;
    }

    protected function saveEntity($entity, $request)
    {
        $this->getService("agit.api.persistence")->saveEntity($entity, $this->getPersistableData($request));
        return $entity;
    }

    protected function getPersistableData($input)
    {
        $output = null;

        if (is_object($input))
        {
            $output = new \stdClass();
            $reqObj = null;

            if ($input instanceof AbstractEntityObject)
            {
                $reqObj = $input;
                $input = $input->getValues();
            }

            foreach ((array)$input as $key => $value)
                if (!$reqObj || !$reqObj->getPropertyMeta($key, "Type")->get("readonly"))
                    $output->$key = $this->getPersistableData($value);
        }
        elseif (is_array($input))
        {
            $output = [];

            foreach ($input as $key => $value)
                $output[$key] = $this->getPersistableData($value);
        }
        else
        {
            $output = $input;
        }

        return $output;
    }
}
