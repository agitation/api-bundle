<?php
/**
 * @package    agitation/api
 * @link       http://github.com/agitation/AgitApiBundle
 * @author     Alex Günsche <http://www.agitsol.com/>
 * @copyright  2012-2015 AGITsol GmbH
 * @license    http://opensource.org/licenses/MIT
 */

namespace Agit\ApiBundle\Annotation\Property;

use Agit\CommonBundle\Exception\InternalErrorException;
use Agit\ApiBundle\Exception\InvalidObjectValueException;

/**
 * @Annotation
 *
 * This annotation can be used to skip certain properties while auto-filling objects.
 */
class View extends AbstractPropertyMeta
{
    /**
     * @var the property should only be set when one of the given views is selected.
     */
    protected $only = [];

    /**
     * @var the property should only be set if none of the given views is selected.
     */
    protected $not = [];
}
