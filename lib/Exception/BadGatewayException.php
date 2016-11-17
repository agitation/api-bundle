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
 * The server was acting as a gateway or proxy and received an invalid response
 * from the upstream server.
 */
class BadGatewayException extends AgitException
{
    protected $statusCode = 502;
}
