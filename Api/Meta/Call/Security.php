<?php
/**
 * @package    agitation/api
 * @link       http://github.com/agitation/AgitApiBundle
 * @author     Alex Günsche <http://www.agitsol.com/>
 * @copyright  2012-2015 AGITsol GmbH
 * @license    http://opensource.org/licenses/MIT
 */

namespace Agit\ApiBundle\Api\Meta\Call;

use Agit\ApiBundle\Api\Meta\AbstractMeta;

/**
 * @Annotation
 */
class Security extends AbstractMeta
{
    /**
     * @var user capability required for this call.
     */
    protected $capability;

    /**
     * @var whether or not to allow cross-origin requests.
     */
    protected $allowCrossOrigin = false;
}
