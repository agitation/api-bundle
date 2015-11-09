<?php
/**
 * @package    agitation/api
 * @link       http://github.com/agitation/AgitApiBundle
 * @author     Alex Günsche <http://www.agitsol.com/>
 * @copyright  2012-2015 AGITsol GmbH
 * @license    http://opensource.org/licenses/MIT
 */

namespace Agit\ApiBundle\Exception;

use Agit\CommonBundle\Exception\AgitException;

/**
 * An update has been requested where a field would be set to a value which must
 * only exist once in a certain context, but there already exists another object
 * with that field value in the same context. For example, it is not possible to
 * subscript to a shop's newsletter twice with the same e-mail address.
 */
class DuplicateUniqueValueException extends AgitException { }