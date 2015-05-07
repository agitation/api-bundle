<?php
/**
 * @package    agitation/api
 * @link       http://github.com/agitation/AgitApiBundle
 * @author     Alex Günsche <http://www.agitsol.com/>
 * @copyright  2012-2015 AGITsol GmbH
 * @license    http://opensource.org/licenses/MIT
 */

namespace Agit\ApiBundle\Api\Meta\Property;

use Agit\ApiBundle\Api\Meta\AbstractMeta;
use Agit\CoreBundle\Exception\InternalErrorException;

/**
 * @Annotation
 */
class Name extends AbstractMeta
{
    /**
     * @var human readable name of the annotated property.
     */
    protected $value;

    public function getName()
    {
        return $this->value;
    }

    public function setName($value)
    {
        $this->value = $value;
    }
}