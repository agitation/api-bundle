<?php
declare(strict_types=1);
/*
 * @package    agitation/api-bundle
 * @link       http://github.com/agitation/api-bundle
 * @author     Alexander Günsche
 * @license    http://opensource.org/licenses/MIT
 */

namespace Agit\ApiBundle\Api\Object;

use Agit\ApiBundle\Exception\InvalidObjectException;

abstract class AbstractEntityObject extends AbstractObject implements EntityObjectInterface, RequestObjectInterface, ResponseObjectInterface
{
    use RequestObjectTrait;
    use ResponseObjectTrait;

    // override this method if the entity has a different ID implementation
    public function getId()
    {
        if (! $this->has('id'))
        {
            throw new InvalidObjectException('An entity object must have an `id` field.');
        }

        return $this->get('id');
    }
}
