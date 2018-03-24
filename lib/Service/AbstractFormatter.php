<?php
declare(strict_types=1);

/*
 * @package    agitation/api-bundle
 * @link       http://github.com/agitation/api-bundle
 * @author     Alexander Günsche
 * @license    http://opensource.org/licenses/MIT
 */

namespace Agit\ApiBundle\Service;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

abstract class AbstractFormatter
{
    public function createResponse(Request $httpRequest, $result)
    {
        $response = new Response();
        $response->headers = $this->getHttpHeaders($httpRequest, $result);
        $response->setContent($this->getHttpContent($httpRequest, $result));

        return $response;
    }

    protected function getHttpHeaders(Request $httpRequest, $result)
    {
        $headers = new ResponseHeaderBag();
        $headers->set('Content-Type', $this->getMimeType() . '; charset=utf-8');

        return $headers;
    }

    abstract protected function getMimeType();

    abstract protected function getHttpContent(Request $httpRequest, $result);
}
