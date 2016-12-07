<?php

/*
 * @package    agitation/api-bundle
 * @link       http://github.com/agitation/api-bundle
 * @author     Alexander Günsche
 * @license    http://opensource.org/licenses/MIT
 */

namespace Agit\ApiBundle\Api\Controller;

use Agit\BaseBundle\Exception\InternalErrorException;

trait EntityAllTrait
{
    public function all()
    {
        if (! ($this instanceof AbstractEntityController)) {
            throw new InternalErrorException("This trait must be used in children of the AbstractEntityController.");
        }

        $this->checkPermissions(null, __FUNCTION__);

        $this->responseService->setView("list");
        $responseObjectName = $this->getResponseObjectApiClass();
        $entities = $this->getEntityManager()->getRepository($this->getEntityClass())->findAll();
        $result = [];

        foreach ($entities as $entity) {
            $resObj = $this->createObject($responseObjectName);
            $resObj->set("id", $entity->getId());
            $resObj->set("name", $entity->getName());

            if ($resObj->has("deleted") && method_exists($entity, "isDeleted")) {
                $resObj->set("deleted", $entity->isDeleted());
            }

            $result[] = $resObj;
        }

        return $result;
    }
}
