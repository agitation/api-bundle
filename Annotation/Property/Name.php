<?php

/*
 * @package    agitation/api-bundle
 * @link       http://github.com/agitation/api-bundle
 * @author     Alexander Günsche
 * @license    http://opensource.org/licenses/MIT
 */

/**
 * @link       http://github.com/agitation/AgitApiBundle
 *
 * @author     Alex Günsche <http://www.agitsol.com/>
 * @copyright  2012-2015 AGITsol GmbH
 * @license    http://opensource.org/licenses/MIT
 */

namespace Agit\ApiBundle\Annotation\Property;

use Agit\IntlBundle\Tool\Translate;

/**
 * @Annotation
 */
class Name extends AbstractPropertyMeta
{
    /**
     * @var human readable name of the annotated property
     */
    protected $value;
    /**
     * @var context, in case the name is ambiguous
     */
    protected $context = "";

    // NOTE: This method returns the translated name. If you want the original
    // string, use `Name::get("value")`.
    public function getName()
    {
        return $this->context
            ? Translate::x($this->context, $this->value)
            : Translate::t($this->value);
    }
}
