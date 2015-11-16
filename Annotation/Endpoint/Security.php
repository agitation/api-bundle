<?php
/**
 * @package    agitation/api
 * @link       http://github.com/agitation/AgitApiBundle
 * @author     Alex Günsche <http://www.agitsol.com/>
 * @copyright  2012-2015 AGITsol GmbH
 * @license    http://opensource.org/licenses/MIT
 */

namespace Agit\ApiBundle\Annotation\Endpoint;

use Agit\CommonBundle\Annotation\SerializableAnnotationInterface;
use Agit\CommonBundle\Annotation\SerializableAnnotationTrait;

/**
 * @Annotation
 */
class Security implements SerializableAnnotationInterface
{
    use SerializableAnnotationTrait;

    /**
     * @var user capability required for this call.
     */
    protected $capability;

    /**
     * @var whether or not to allow cross-origin requests without a CSRF token.
     */
    protected $allowCrossOrigin = false;
}
