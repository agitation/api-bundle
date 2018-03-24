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
 * The CSRF check has failed, probably due to a missing or incorrect CSRF token.
 */
class CsrfException extends PublicException
{
    protected $statusCode = 401;
}
