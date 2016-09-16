<?php

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
 * This annotation can be used to skip certain properties while auto-filling objects.
 */
class View extends AbstractPropertyMeta
{
    /**
     * @var the property should only be set when one of the given views is selected.
     */
    protected $only = [];

    /**
     * @var the property should only be set if none of the given views is selected.
     */
    protected $not = [];
}
