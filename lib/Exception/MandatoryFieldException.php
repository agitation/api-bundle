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
 * A field, although technically valid, does not have a neccessary value to
 * continue processing. This happens usually on boolean fields that should
 * indicate that a user has accepted certain legal terms.
 */
class MandatoryFieldException extends AgitException
{
    protected $httpStatus = 400;
}
