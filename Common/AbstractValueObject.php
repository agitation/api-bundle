<?php

/*
 * @package    agitation/api-bundle
 * @link       http://github.com/agitation/api-bundle
 * @author     Alexander Günsche
 * @license    http://opensource.org/licenses/MIT
 */

namespace Agit\ApiBundle\Common;

abstract class AbstractValueObject extends AbstractObject implements RequestObjectInterface, ResponseObjectInterface
{
    use RequestObjectTrait;
    use ResponseObjectTrait;
}
