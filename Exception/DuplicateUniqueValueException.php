<?php

/*
 * @package    agitation/api-bundle
 * @link       http://github.com/agitation/api-bundle
 * @author     Alexander Günsche
 * @license    http://opensource.org/licenses/MIT
 */

/**
 * @link       http://github.com/agitation/AgitApiBundle
 *
 * @author     Alex Günsche <http://www.agitsol.com/>
 * @copyright  2012-2015 AGITsol GmbH
 * @license    http://opensource.org/licenses/MIT
 */

namespace Agit\ApiBundle\Exception;

use Agit\BaseBundle\Exception\AgitException;

/**
 * An update has been requested where a field would be set to a value which must
 * only exist once in a certain context, but there already exists another object
 * with that field value in the same context.
 */
class DuplicateUniqueValueException extends AgitException
{
    protected $httpStatus = 409;
}
