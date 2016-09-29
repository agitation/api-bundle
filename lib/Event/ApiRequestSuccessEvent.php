<?php

namespace Agit\ApiBundle\Event;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ApiRequestSuccessEvent extends AbstractApiEvent
{
    private $resultData;

    public function __construct(Request $request, Response $response, $endpointName, $requestData, $resultData, $time, $memory)
    {
        parent::__construct($request, $response, $endpointName, $requestData, $time, $memory);
        $this->resultData = $resultData;
    }

    public function getResultData()
    {
        return $this->resultData;
    }
}
