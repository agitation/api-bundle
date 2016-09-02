<?php
/**
 * @package    agitation/api
 * @link       http://github.com/agitation/AgitApiBundle
 * @author     Alex Günsche <http://www.agitsol.com/>
 * @copyright  2012-2015 AGITsol GmbH
 * @license    http://opensource.org/licenses/MIT
 */

namespace Agit\ApiBundle\Common;

use stdClass;
use Agit\BaseBundle\Exception\InternalErrorException;
use Agit\ApiBundle\Annotation\MetaContainer;

abstract class AbstractResponseObject extends AbstractObject implements ResponseObjectInterface
{
    use ResponseObjectTrait;
}
