<?php
/**
 * @package    agitation/api
 * @link       http://github.com/agitation/AgitApiBundle
 * @author     Alex Günsche <http://www.agitsol.com/>
 * @copyright  2012-2015 AGITsol GmbH
 * @license    http://opensource.org/licenses/MIT
 */

namespace Agit\ApiBundle\Common\Property;

use Agit\ApiBundle\Annotation\Property;

trait DeletedTrait
{
    /**
     * @Property\Name("Deleted")
     * @Property\BooleanType(readonly=true)
     */
    public $deleted;
}
