<?php
declare(strict_types=1);
/*
 * @package    agitation/api-bundle
 * @link       http://github.com/agitation/api-bundle
 * @author     Alexander Günsche
 * @license    http://opensource.org/licenses/MIT
 */

namespace Agit\ApiBundle\Exception;

use Agit\BaseBundle\Exception\PublicException;

/**
 * A non-existent endpoint was called.
 */
class InvalidEndpointException extends PublicException
{
    protected $statusCode = 404;
}
