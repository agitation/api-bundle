<?php

/*
 * @package    agitation/api-bundle
 * @link       http://github.com/agitation/api-bundle
 * @author     Alexander Günsche
 * @license    http://opensource.org/licenses/MIT
 */

namespace Agit\ApiBundle\Exception;

use Agit\BaseBundle\Exception\PublicException;

/**
 * The request cannot be processed, because it would cause an inconsistency
 * between certain objects or contexts.
 */
class ConsistencyException extends PublicException
{
    protected $statusCode = 409;
}
