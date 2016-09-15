<?php

/*
 * @package    agitation/api-bundle
 * @link       http://github.com/agitation/api-bundle
 * @author     Alexander Günsche
 * @license    http://opensource.org/licenses/MIT
 */

namespace Agit\ApiBundle\Api\Controller;

use Agit\ApiBundle\Annotation\MetaContainer;
use Agit\ApiBundle\Service\ResponseService;

abstract class AbstractController
{
    /**
     * @var full endpoint name, e.g. `foobar.v1/foo.bar`.
     */
    protected $name;

    /**
     * @var API namespace.
     */
    protected $apiNamespace;

    /**
     * @var instance of the response generation service.
     */
    protected $responseService;

    public function init($name, MetaContainer $meta, ResponseService $responseService)
    {
        $this->endpointName = $name;
        $this->meta = $meta;
        $this->apiNamespace = strstr($name, "/", true);
        $this->responseService = $responseService;
    }

    protected function getMeta($name)
    {
        return $this->meta->get($name);
    }

    protected function createObject($name, $data = null)
    {
        if (strpos($name, "/") === false) {
            $name = "{$this->apiNamespace}/$name";
        }

        return $this->responseService->createResponseObject($name, $data);
    }
}
