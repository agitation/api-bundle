<?php
declare(strict_types=1);

/*
 * @package    agitation/api-bundle
 * @link       http://github.com/agitation/api-bundle
 * @author     Alexander Günsche
 * @license    http://opensource.org/licenses/MIT
 */

namespace Agit\ApiBundle\Api\Controller;

use Agit\ApiBundle\Api\Object\AbstractEntityObject;
use Agit\BaseBundle\Exception\InternalErrorException;
use Exception;

trait EntityCreateTrait
{
    public function create(AbstractEntityObject $request)
    {
        if (! ($this instanceof AbstractEntityController))
        {
            throw new InternalErrorException('This trait must be used in children of the AbstractEntityController.');
        }

        $this->checkPermissions($request, __FUNCTION__);
        $this->validate($request);

        try
        {
            $this->getEntityManager()->beginTransaction();

            $entity = $this->createEntity($request);

            $this->getEntityManager()->commit();
        }
        catch (Exception $e)
        {
            $this->getEntityManager()->rollBack();

            throw $e;
        }

        return $this->createObject($this->getResponseObjectApiClass(), $entity);
    }

    protected function createEntity(AbstractEntityObject $request)
    {
        $className = $this->getEntityManager()->getClassMetadata($this->getEntityClass())->getName();
        $entity = $this->saveEntity(new $className(), $request);

        return $entity;
    }
}
