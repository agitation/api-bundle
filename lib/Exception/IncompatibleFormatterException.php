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
 * The client has requested to format the response in a format which is
 * incompatible with the data type of the result.
 */
class IncompatibleFormatterException extends PublicException
{
    protected $statusCode = 406;
}
