<?php

namespace Agit\ApiBundle\Event;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\EventDispatcher\Event;

class AbstractApiEvent extends Event
{
    private $request;
    private $response;
    private $endpointName;
    private $requestData;
    private $time;
    private $memory;

    public function __construct(Request $request, Response $response, $endpointName, $requestData, $time, $memory)
    {
        $this->request = $request;
        $this->response = $response;
        $this->endpointName = $endpointName;
        $this->requestData = $requestData;
        $this->time = $time;
        $this->memory = $memory;
    }

    public function getRequest()
    {
        return $this->request;
    }

    public function getEndpointName()
    {
        return $this->endpointName;
    }

    public function getRequestData()
    {
        return $this->requestData;
    }

    public function getTime()
    {
        return $this->time;
    }

    public function getMemory()
    {
        return $this->memory;
    }

    /**
     * Get the value of Response
     *
     * @return mixed
     */
    public function getResponse()
    {
        return $this->response;
    }
}
