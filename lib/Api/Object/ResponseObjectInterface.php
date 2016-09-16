<?php

/*
 * @package    agitation/api-bundle
 * @link       http://github.com/agitation/api-bundle
 * @author     Alexander Günsche
 * @license    http://opensource.org/licenses/MIT
 */

namespace Agit\ApiBundle\Api\Object;

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
