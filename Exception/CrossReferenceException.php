<?php

/*
 * @package    agitation/api-bundle
 * @link       http://github.com/agitation/api-bundle
 * @author     Alexander Günsche
 * @license    http://opensource.org/licenses/MIT
 */

namespace Agit\ApiBundle\Exception;

use Agit\BaseBundle\Exception\AgitException;

/**
 * An object has been requested which is incompatible with other objects in the
 * current context.
 */
class CrossReferenceException extends AgitException
{
    protected $httpStatus = 409;
}
