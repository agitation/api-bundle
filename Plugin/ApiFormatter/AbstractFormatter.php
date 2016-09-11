<?php

/*
 * @package    agitation/api-bundle
 * @link       http://github.com/agitation/api-bundle
 * @author     Alexander Günsche
 * @license    http://opensource.org/licenses/MIT
 */

/**
 * @link       http://github.com/agitation/AgitApiBundle
 *
 * @author     Alex Günsche <http://www.agitsol.com/>
 * @copyright  2012-2015 AGITsol GmbH
 * @license    http://opensource.org/licenses/MIT
 */

namespace Agit\ApiBundle\Plugin\ApiFormatter;

use Agit\ApiBundle\Annotation\MetaContainer;
use Agit\ApiBundle\Common\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

abstract class AbstractFormatter
{
    protected $meta;

    protected $controller;

    protected $request;

    protected $debug;

    public function __construct(MetaContainer $meta, AbstractController $controller, Request $request, $debug)
    {
        $this->meta = $meta;
        $this->controller = $controller;
        $this->request = $request;
        $this->debug = $debug;
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
