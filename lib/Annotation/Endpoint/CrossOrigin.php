<?php
declare(strict_types=1);

/*
 * @package    agitation/api-bundle
 * @link       http://github.com/agitation/api-bundle
 * @author     Alexander Günsche
 * @license    http://opensource.org/licenses/MIT
 */

namespace Agit\ApiBundle\Annotation\Endpoint;

/**
 * @Annotation
 */
class CrossOrigin extends AbstractEndpointMeta
{
    /**
     * @var string "none"|"all"|"some"
     *
     * NOTE: "some" is not yet supported and is treated like "none"
     */
    protected $allow = 'none';
}
