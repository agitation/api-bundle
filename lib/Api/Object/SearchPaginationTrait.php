<?php
declare(strict_types=1);

/*
 * @package    agitation/api-bundle
 * @link       http://github.com/agitation/api-bundle
 * @author     Alexander Günsche
 * @license    http://opensource.org/licenses/MIT
 */

namespace Agit\ApiBundle\Api\Object;

use Agit\ApiBundle\Annotation\Property;

trait SearchPaginationTrait
{
    /**
     * @Property\IntegerType(minValue=0)
     */
    public $offset = 0;

    /**
     * @Property\IntegerType(minValue=1, maxValue=200)
     */
    public $limit = 50;
}
