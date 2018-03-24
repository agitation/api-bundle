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
 *
 * Container for raw data. ATTENTION: Handle with care! This should not be used
 * in request objects. And if is unavoidable, proper validation must be applied!
 */
class RawType extends AbstractType
{
    protected $nullable = true;

    public function check($value)
    {
        $this->init($value);
    }
}
