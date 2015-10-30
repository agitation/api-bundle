<?php
/**
 * @package    agitation/api
 * @link       http://github.com/agitation/AgitApiBundle
 * @author     Alex Günsche <http://www.agitsol.com/>
 * @copyright  2012-2015 AGITsol GmbH
 * @license    http://opensource.org/licenses/MIT
 */

namespace Agit\ApiBundle\Api\Formatter;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Agit\ApiBundle\Api\Endpoint\AbstractEndpoint;
use Agit\CoreBundle\Exception\InternalErrorException;

abstract class AbstractFormatter
{
    static protected $format;

    static protected $mimeType;

    protected $container;

    protected $endpoint;

    protected $request;

    static public function getFormat()
    {
        return static::$format;
    }

    static public function getMimeType()
    {
        return static::$mimeType;
    }

    public function __construct(ContainerInterface $container, AbstractEndpoint $endpoint, Request $request)
    {
        if (!static::$format || !static::$mimeType)
            throw new InternalErrorException("'format' and 'mimeType' must be set in the concrete formatter class.");

        $this->endpoint = $endpoint;
        $this->container = $container;
        $this->request = $request;
    }

    public function getResponse()
    {
        $response = new Response();
        $response->headers = $this->getHttpHeaders();
        $response->setContent($this->getHttpContent());
        return $response;
    }

    abstract protected function getHttpHeaders();

    abstract protected function getHttpContent();
}
