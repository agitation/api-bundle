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
 * The requested resource must not be accessed by the currently authenticated
 * user.
 */
class ForbiddenException extends PublicException
{
    protected $statusCode = 403;
}
