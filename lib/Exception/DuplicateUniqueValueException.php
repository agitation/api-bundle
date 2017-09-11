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
 * An update has been requested where a field would be set to a value which must
 * only exist once in a certain context, but there already exists another object
 * with that field value in the same context.
 */
class DuplicateUniqueValueException extends PublicException
{
    protected $statusCode = 409;
}
