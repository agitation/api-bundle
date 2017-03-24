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
use Exception;
use Psr\Log\LogLevel;

trait EntityCreateTrait
{
    public function create(AbstractEntityObject $requestObject)
    {
        if (! ($this instanceof AbstractEntityController)) {
            throw new InternalErrorException("This trait must be used in children of the AbstractEntityController.");
        }

        $this->checkPermissions($requestObject, __FUNCTION__);
        $this->validate($requestObject);

        try {
            $this->getEntityManager()->beginTransaction();

            $className = $this->getEntityManager()->getClassMetadata($this->getEntityClass())->getName();
            $entity = $this->saveEntity(new $className(), $requestObject);

            $this->getLogger()->log(
                LogLevel::NOTICE,
                "agit.api.entity",
                sprintf(Translate::xl("1: object type, 2: name", '%1$s “%2$s” has been created.'), $this->getEntityClassName($entity), $this->getEntityName($entity)),
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
