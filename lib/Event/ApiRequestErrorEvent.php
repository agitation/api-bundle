<?php

namespace Agit\ApiBundle\Event;

use Exception;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ApiRequestErrorEvent extends AbstractApiEvent
{
    protected $exception;

    public function __construct(Request $request, Response $response, $endpointName, $requestData, Exception $exception, $time, $memory)
    {
        parent::__construct($request, $response, $endpointName, $requestData, $time, $memory);
        $this->exception = $exception;
    }

    public function getException()
    {
        return $this->exception;
    }
}
