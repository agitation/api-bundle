<?php
/**
 * @package    agitation/api
 * @link       http://github.com/agitation/AgitApiBundle
 * @author     Alex Günsche <http://www.agitsol.com/>
 * @copyright  2012-2015 AGITsol GmbH
 * @license    http://opensource.org/licenses/MIT
 */

namespace Agit\ApiBundle\Api\Meta\Property;

use Agit\CoreBundle\Exception\InternalErrorException;
use Agit\IntlBundle\Translate;
use Agit\ApiBundle\Api\Meta\AbstractMeta;

/**
 * @Annotation
 */
class Name extends AbstractMeta
{
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
