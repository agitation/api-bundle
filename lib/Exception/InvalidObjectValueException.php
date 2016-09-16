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
 * An API request object has an invalid value or doesn't match the required format.
 */
class InvalidObjectValueException extends AgitException
{
    protected $httpStatus = 400;
}
