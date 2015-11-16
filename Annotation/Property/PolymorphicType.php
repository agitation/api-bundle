<?php
/**
 * @package    agitation/api
 * @link       http://github.com/agitation/AgitApiBundle
 * @author     Alex Günsche <http://www.agitsol.com/>
 * @copyright  2012-2015 AGITsol GmbH
 * @license    http://opensource.org/licenses/MIT
 */

namespace Agit\ApiBundle\Annotation\Property;

/**
 * @Annotation
 */
class PolymorphicType extends AbstractType
{
    protected $nullable = true;

    public function check($value)
    {
        $this->init($value);

        // other checks are performed in the respecitve endpoint
    }
}