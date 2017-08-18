<?php

/*
 * @package    agitation/api-bundle
 * @link       http://github.com/agitation/api-bundle
 * @author     Alexander Günsche
 * @license    http://opensource.org/licenses/MIT
 */

namespace Agit\ApiBundle\Service;

use Symfony\Component\HttpFoundation\Request;

abstract class AbstractSerializableFormatter extends AbstractFormatter
{
    protected $objectMetaService;

    protected $debug;

    public function __construct(ObjectMetaService $objectMetaService, $debug)
    {
        $this->objectMetaService = $objectMetaService;
        $this->debug = $debug;
    }

    protected function getHttpContent(Request $httpRequest, $result)
    {
        if (! $this->debug && $result && ! is_scalar($result) && $httpRequest->headers->get("x-api-serialize-compact", null, true) === "true") {
            $compactor = new Compactor($result);
            $result = $this->objectMetaService->createObject("common.v1/Response");
            $result->setPayload($compactor->getPayload());
            $result->setEntityList($compactor->getEntities());
        }

        return $this->encode($result);
    }

    abstract protected function encode($result);
}
