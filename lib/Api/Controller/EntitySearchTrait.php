<?php
declare(strict_types=1);

/*
 * @package    agitation/api-bundle
 * @link       http://github.com/agitation/api-bundle
 * @author     Alexander Günsche
 * @license    http://opensource.org/licenses/MIT
 */

namespace Agit\ApiBundle\Api\Controller;

use Agit\ApiBundle\Api\Object\RequestObjectInterface;
use Agit\BaseBundle\Exception\InternalErrorException;

trait EntitySearchTrait
{
    public function search(RequestObjectInterface $requestObject)
    {
        if (! ($this instanceof AbstractEntityController))
        {
            throw new InternalErrorException('This trait must be used in children of the AbstractEntityController.');
        }

        $this->checkPermissions($requestObject, __FUNCTION__);
        $this->responseService->setView('search');

        return $this->createResultList(
            $this->getResponseObjectApiClass(),
            $this->createSearchQuery($requestObject)->getQuery()->getResult()
        );
    }
}
