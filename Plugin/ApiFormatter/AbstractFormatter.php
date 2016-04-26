<?php
/**
 * @package    agitation/api
 * @link       http://github.com/agitation/AgitApiBundle
 * @author     Alex Günsche <http://www.agitsol.com/>
 * @copyright  2012-2015 AGITsol GmbH
 * @license    http://opensource.org/licenses/MIT
 */

namespace Agit\ApiBundle\Plugin\ApiFormatter;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Agit\ApiBundle\Common\AbstractController;
use Agit\ApiBundle\Annotation\MetaContainer;

abstract class AbstractFormatter
{
    protected $meta;

    protected $endpointClass;

    protected $request;

    public function __construct(MetaContainer $meta, AbstractController $endpointClass, Request $request)
    {
        $this->meta = $meta;
        $this->endpointClass = $endpointClass;
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
