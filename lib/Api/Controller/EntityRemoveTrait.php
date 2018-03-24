<?php
declare(strict_types=1);

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
use Exception;
use Psr\Log\LogLevel;

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

            $this->getLogger()->log(
                LogLevel::NOTICE,
                'agit.api.entity',
                sprintf(Translate::xl('1: object type, 2: name', '%1$s “%2$s” has been removed permanently.'), $this->getEntityClassName($entity), $this->getEntityName($entity)),
                true
            );

            $this->getEntityManager()->commit();
        }
        catch (Exception $e)
        {
            $this->getEntityManager()->rollBack();

            throw new BadRequestException(Translate::t('This object cannot be removed, because there are other objects depending on it.'));
        }
    }
}
