<?php
declare(strict_types=1);

/*
 * @package    agitation/api-bundle
 * @link       http://github.com/agitation/api-bundle
 * @author     Alexander Günsche
 * @license    http://opensource.org/licenses/MIT
 */

namespace Agit\ApiBundle\Api\Controller;

use Agit\BaseBundle\Exception\InternalErrorException;
use Agit\IntlBundle\Tool\Translate;
use Exception;

use Symfony\Component\HttpKernel\Exception\ConflictHttpException;

trait EntityRemoveTrait
{
    public function remove($id)
    {
        if (! ($this instanceof AbstractEntityController))
        {
            throw new InternalErrorException('This trait must be used in children of the AbstractEntityController.');
        }

        $this->checkPermissions($id, __FUNCTION__);
        $entity = $this->retrieveEntity($this->getEntityClass(), $id);

        try
        {
            $this->getEntityManager()->beginTransaction();

            $this->getEntityManager()->remove($entity);
            $this->getEntityManager()->flush();

            $this->getEntityManager()->commit();
        }
        catch (Exception $e)
        {
            $this->getEntityManager()->rollBack();

            throw new ConflictHttpException(Translate::t('This object cannot be removed, because there are other objects depending on it.'));
        }
    }
}
