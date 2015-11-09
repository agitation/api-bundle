<?php
/**
 * @package    agitation/api
 * @link       http://github.com/agitation/AgitApiBundle
 * @author     Alex Günsche <http://www.agitsol.com/>
 * @copyright  2012-2015 AGITsol GmbH
 * @license    http://opensource.org/licenses/MIT
 */

namespace Agit\ApiBundle\Api\Object;

use Agit\CommonBundle\Exception\InternalErrorException;
use Agit\ApiBundle\Api\Meta\Property;

/**
 * This is a special object that serves as a collector of uniform items.
 */
class ObjectList extends AbstractObject
{
    /**
     * @Property\PolymorphicType
     */
    public $itemList = array();

    public function add($key, $value)
    {
        if ($key === 'itemList')
            $this->itemList[] = $value;
        else
            parent::add($key, $value);
    }
}