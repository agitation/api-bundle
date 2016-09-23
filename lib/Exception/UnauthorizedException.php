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
 * The requested ressource requires authentication, but the client did not
 * authenticate and is therefore considered unauthorized. After authentication,
 * the client may try again.
 */
class UnauthorizedException extends AgitException
{
    protected $httpStatus = 401;
}