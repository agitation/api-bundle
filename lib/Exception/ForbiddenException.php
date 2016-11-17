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
 * The requested resource must not be accessed by the currently authenticated
 * user.
 */
class ForbiddenException extends AgitException
{
    protected $statusCode = 403;
}
