<?php

/*
 * @package    agitation/api-bundle
 * @link       http://github.com/agitation/api-bundle
 * @author     Alexander Günsche
 * @license    http://opensource.org/licenses/MIT
 */

namespace Agit\ApiBundle\Api\Controller;

use Agit\ApiBundle\Exception\BadRequestException;
use Agit\BaseBundle\Exception\InternalErrorException;
use Agit\IntlBundle\Tool\Translate;
use Agit\LoggingBundle\Service\Logger;
use Doctrine\ORM\EntityManager;
use Exception;
use Psr\Log\LogLevel;

trait EntityRemoveTrait
{
    public function remove($id)
    {
        if (! ($this instanceof AbstractEntityController)) {
            throw new InternalErrorException("This trait must be used in children of the AbstractEntityController.");
        }

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
}
