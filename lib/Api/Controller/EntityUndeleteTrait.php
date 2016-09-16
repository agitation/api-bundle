<?php

/*
 * @package    agitation/api-bundle
 * @link       http://github.com/agitation/api-bundle
 * @author     Alexander Günsche
 * @license    http://opensource.org/licenses/MIT
 */

namespace Agit\ApiBundle\Api\Controller;

use Agit\ApiBundle\Exception\BadRequestException;
use Agit\BaseBundle\Entity\DeletableInterface;
use Agit\BaseBundle\Exception\InternalErrorException;
use Agit\IntlBundle\Tool\Translate;
use Agit\LoggingBundle\Service\Logger;
use Doctrine\ORM\EntityManager;
use Exception;
use Psr\Log\LogLevel;

trait EntityUndeleteTrait
{
    public function undelete($id)
    {
        if (! ($this instanceof AbstractEntityController)) {
            throw new InternalErrorException("This trait must be used in children of the AbstractEntityController.");
        }

        $this->checkPermissions($id, __FUNCTION__);

        try {
            $this->getEntityManager()->beginTransaction();

            $entity = $this->retrieveEntity($this->getEntityClass(), $id);

            if (! ($entity instanceof DeletableInterface)) {
                throw new InternalErrorException("Only entities which implement the DeletableInterface can be undeleted here.");
            }

            if (! $entity->isDeleted()) {
                throw new BadRequestException(Translate::t("This entity is not deleted and hence cannot be undeleted."));
            }

            $entity->setDeleted(false);
            $this->getEntityManager()->persist($entity);
            $this->getEntityManager()->flush();

            $this->getLogger()->log(
                LogLevel::WARNING,
                "agit.api.entity",
                sprintf(Translate::tl("Object %s of type %s has been undeleted."), $entity->getId(), $this->getEntityClassName($entity)),
                true
            );

            $this->getEntityManager()->commit();
        } catch (Exception $e) {
            $this->getEntityManager()->rollBack();
            throw $e;
        }

        return true;
    }
}
