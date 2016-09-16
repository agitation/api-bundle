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
 * The client has requested to format the response in a format which is
 * incompatible with the data type of the result.
 */
class IncompatibleFormatterException extends AgitException
{
    protected $httpStatus = 406;
}
