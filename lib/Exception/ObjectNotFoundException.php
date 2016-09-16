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
 * A non-existent object has been requested or referenced.
 */
class ObjectNotFoundException extends AgitException
{
    protected $httpStatus = 404;
}
