<?php

/*
 * @package    agitation/api-bundle
 * @link       http://github.com/agitation/api-bundle
 * @author     Alexander Günsche
 * @license    http://opensource.org/licenses/MIT
 */

/**
 * @link       http://github.com/agitation/AgitApiBundle
 *
 * @author     Alex Günsche <http://www.agitsol.com/>
 * @copyright  2012-2015 AGITsol GmbH
 * @license    http://opensource.org/licenses/MIT
 */

namespace Agit\ApiBundle\Common;

use Agit\ApiBundle\Service\ResponseService;

trait ResponseObjectTrait
{
    /**
     * @var ObjectMetaService instance
     */
    private $responseService;

    public function setResponseService(ResponseService $responseService)
    {
        $this->responseService = $responseService;
    }

    /**
     * Can be overridden by API objects which have their own logic of matching
     * the given $data to their properties.
     */
    public function fill($data)
    {
        if ($this->responseService) {
            if ($this->responseService->isEntity($data)) {
                $this->responseService->fillObjectFromEntity($this, $data);
            } elseif ($data instanceof stdClass) {
                $this->responseService->fillObjectFromPlainObject($this, $data);
            }
        }
    }

    protected function createObject($name, $data = null)
    {
        if (strpos($name, '/') === false) {
            $name = "{$this->apiNamespace}/$name";
        }

        return $this->responseService->createResponseObject($name, $data);
    }
}
