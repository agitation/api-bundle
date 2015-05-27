<?php
/**
 * @package    agitation/api
 * @link       http://github.com/agitation/AgitApiBundle
 * @author     Alex Günsche <http://www.agitsol.com/>
 * @copyright  2012-2015 AGITsol GmbH
 * @license    http://opensource.org/licenses/MIT
 */

namespace Agit\ApiBundle\Api\Object;

use Agit\CoreBundle\Exception\InternalErrorException;
use Agit\ApiBundle\Api\Meta\Object;
use Agit\ApiBundle\Api\Meta\Property;

/**
 * @Object\Object(isScalar=true)
 *
 * A simple string value.
 */
class String extends AbstractObject
{
    /**
     * @Property\StringType
     */
    public $value;
}
