<?php

/*
 * @package    agitation/api-bundle
 * @link       http://github.com/agitation/api-bundle
 * @author     Alexander Günsche
 * @license    http://opensource.org/licenses/MIT
 */

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
