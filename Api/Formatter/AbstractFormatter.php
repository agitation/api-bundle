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

    protected $Container;

    protected $Endpoint;

    protected $Request;

    static public function getFormat()
    {
        return static::$format;
    }

    static public function getMimeType()
    {
        return static::$mimeType;
    }

    public function __construct(ContainerInterface $Container, AbstractEndpoint $Endpoint, Request $Request)
    {
        if (!static::$format || !static::$mimeType)
            throw new InternalErrorException("'format' and 'mimeType' must be set in the concrete formatter class.");

        $this->Endpoint = $Endpoint;
        $this->Container = $Container;
        $this->Request = $Request;
    }

    public function getResponse()
    {
        $Response = new Response();
        $Response->headers = $this->getHttpHeaders();
        $Response->setContent($this->getHttpContent());
        return $Response;
    }

    abstract protected function getHttpHeaders();

    abstract protected function getHttpContent();
}
