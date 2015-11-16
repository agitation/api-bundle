<?php
/**
 * @package    agitation/api
 * @link       http://github.com/agitation/AgitApiBundle
 * @author     Alex Günsche <http://www.agitsol.com/>
 * @copyright  2012-2015 AGITsol GmbH
 * @license    http://opensource.org/licenses/MIT
 */

namespace Agit\ApiBundle\Annotation\Property;

use Agit\CommonBundle\Annotation\SerializableAnnotationInterface;
use Agit\CommonBundle\Annotation\SerializableAnnotationTrait;
use Agit\CommonBundle\Exception\InternalErrorException;
use Agit\IntlBundle\Translate;

/**
 * @Annotation
 */
class Name implements SerializableAnnotationInterface
{
    use SerializableAnnotationTrait;

    /**
     * @var human readable name of the annotated property
     */
    protected $value;
    /**
     * @var context, in case the name is ambiguous
     */
    protected $context = '';

    // NOTE: This method returns the translated name. If you want the original
    // string, use `Name::get('value')`.
    public function getName()
    {
        return $this->context
            ? Translate::x($this->value, $this->context)
            : Translate::t($this->value);
    }
}