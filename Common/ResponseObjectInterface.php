<?php
/**
 * @package    agitation/api
 * @link       http://github.com/agitation/AgitApiBundle
 * @author     Alex Günsche <http://www.agitsol.com/>
 * @copyright  2012-2015 AGITsol GmbH
 * @license    http://opensource.org/licenses/MIT
 */

namespace Agit\ApiBundle\Common;

use Agit\ApiBundle\Service\ResponseService;

interface ResponseObjectInterface
{
    public function setResponseService(ResponseService $responseService);

    /**
     * Can be overridden by API objects which have their own logic of matching
     * the given $data to their properties.
     */
    public function fill($data);
}
