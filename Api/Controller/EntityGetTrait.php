<?php

/*
 * @package    agitation/api-bundle
 * @link       http://github.com/agitation/api-bundle
 * @author     Alexander Günsche
 * @license    http://opensource.org/licenses/MIT
 */

namespace Agit\ApiBundle\Api\Controller;

use Agit\BaseBundle\Exception\InternalErrorException;

trait EntityGetTrait
{
    public function get($id)
    {
        if (! ($this instanceof AbstractEntityController)) {
            throw new InternalErrorException("This trait must be used in children of the AbstractEntityController.");
        }

        $this->checkPermissions($id, __FUNCTION__);
        $entity = $this->retrieveEntity($this->getEntityClass(), $id);

        return $this->createObject($this->getResponseObjectApiClass(), $entity);
    }
}
