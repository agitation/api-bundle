<?php
declare(strict_types=1);
/*
 * @package    agitation/api-bundle
 * @link       http://github.com/agitation/api-bundle
 * @author     Alexander Günsche
 * @license    http://opensource.org/licenses/MIT
 */

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
