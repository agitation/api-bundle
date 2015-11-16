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
class Form extends AbstractPropertyMeta
{
    /**
     * @var element type, one of the common HTML form elements
     */
    protected $type;

    /**
     * @var if the form element accepts a fixed set of values, this property should contain a hashmap (key => name) of them.
     */
    protected $values;
}
