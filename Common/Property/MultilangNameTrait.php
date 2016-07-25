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

trait MultilangNameTrait
{
    /**
     * @Property\Name("Name")
     * @Property\MultilangStringType(maxLength=100)
     */
    public $name;
}
