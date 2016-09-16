<?php

/*
 * @package    agitation/api-bundle
 * @link       http://github.com/agitation/api-bundle
 * @author     Alexander Günsche
 * @license    http://opensource.org/licenses/MIT
 */

namespace Agit\ApiBundle\Api\Controller;

use Agit\ApiBundle\Api\Object\AbstractEntityObject;
use Agit\BaseBundle\Exception\InternalErrorException;
use Agit\IntlBundle\Tool\Translate;
use Agit\LoggingBundle\Service\Logger;
use Doctrine\ORM\EntityManager;
use Exception;
use Psr\Log\LogLevel;

trait EntityUpdateTrait
{
    public function update(AbstractEntityObject $requestObject)
    {
        if (! ($this instanceof AbstractEntityController)) {
            throw new InternalErrorException("This trait must be used in children of the AbstractEntityController.");
        }

        $this->checkPermissions($requestObject, __FUNCTION__);
        $this->validate($requestObject);

        try {
            $this->getEntityManager()->beginTransaction();

            $entity = $this->retrieveEntity($this->getEntityClass(), $requestObject->get("id"));
            $entity = $this->saveEntity($entity, $requestObject);

            $this->getLogger()->log(
                LogLevel::WARNING,
                "agit.api.entity",
                sprintf(Translate::tl("Object “%s” of type “%s” has been updated."), $this->getEntityName($entity), $this->getEntityClassName($entity)),
                true
            );

            $this->getEntityManager()->commit();
        } catch (Exception $e) {
            $this->getEntityManager()->rollBack();
            throw $e;
        }

        return $this->createObject($this->getResponseObjectApiClass(), $entity);
    }
}
