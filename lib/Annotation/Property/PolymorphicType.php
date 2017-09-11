<?php
declare(strict_types=1);
/*
 * @package    agitation/api-bundle
 * @link       http://github.com/agitation/api-bundle
 * @author     Alexander Günsche
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
