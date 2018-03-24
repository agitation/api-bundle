<?php
declare(strict_types=1);

/*
 * @package    agitation/api-bundle
 * @link       http://github.com/agitation/api-bundle
 * @author     Alexander Günsche
 * @license    http://opensource.org/licenses/MIT
 */

namespace Agit\ApiBundle\Api\Object;

abstract class AbstractValueObject extends AbstractObject implements RequestObjectInterface, ResponseObjectInterface
{
    use RequestObjectTrait;
    use ResponseObjectTrait;
}
