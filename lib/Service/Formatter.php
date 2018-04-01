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
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class Formatter
{
    /**
     * @var ObjectMetaService
     */
    protected $objectMetaService;

    /**
     * @var bool
     */
    protected $debug;

    public function __construct(ObjectMetaService $objectMetaService, $debug)
    {
        $this->objectMetaService = $objectMetaService;
        $this->debug = $debug;
    }

    public function createResponse(Request $request, $result) : Response
    {
        $response = new Response();

        $response->headers->set('Content-Type', 'application/json; charset=utf-8');

        if (! $this->debug && $result && ! is_scalar($result) && $request->headers->get('x-api-serialize-compact', null, true) === 'true')
        {
            $compactor = new Compactor($result);
            $result = $this->objectMetaService->createObject('common.v1/Response');
            $result->setPayload($compactor->getPayload());
            $result->setEntityList($compactor->getEntities());
        }

        $jsonFlags = JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE;

        if (! $this->debug)
        {
            $response->headers->set('Content-Encoding', 'deflate');
            $content = zlib_encode($content, ZLIB_ENCODING_DEFLATE);
        }
        else
        {
            $jsonFlags += JSON_PRETTY_PRINT;
        }

        $response->setContent(json_encode($result, $jsonFlags));

        return $response;
    }
}
