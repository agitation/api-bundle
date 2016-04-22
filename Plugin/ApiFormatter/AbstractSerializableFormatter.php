<?php
/**
 * @package    agitation/api
 * @link       http://github.com/agitation/AgitApiBundle
 * @author     Alex Günsche <http://www.agitsol.com/>
 * @copyright  2012-2015 AGITsol GmbH
 * @license    http://opensource.org/licenses/MIT
 */

namespace Agit\ApiBundle\Plugin\ApiFormatter;

use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Agit\PluggableBundle\Strategy\ServiceAwarePluginInterface;
use Agit\PluggableBundle\Strategy\ServiceAwarePluginTrait;
use Agit\PluggableBundle\Strategy\Depends;

/**
 * @Depends({"agit.api.objectmeta"})
 */
abstract class AbstractSerializableFormatter extends AbstractFormatter implements ServiceAwarePluginInterface
{
    use ServiceAwarePluginTrait;

    protected function getHttpHeaders()
    {
        $headers = new ResponseHeaderBag();
        $headers->set("Content-Type", $this->meta->get("Formatter")->get("mimeType") . "; charset=utf-8");

        return $headers;
    }

    protected function getHttpContent()
    {
        $compactHeader = $this->request->headers->get("x-api-serialize-compact", null, true);
        $response = $this->endpointClass->getResponse();

        if ($compactHeader === "true")
        {
            list($payload, $entityList) = $this->compactEntities($response);

            $response = $this->getService("agit.api.objectmeta")->createObject("common.v1/Response");
            $response->setPayload($payload);
            $response->setEntityList($entityList);
        }

        return $this->getEncoder()->encode($response, $this->meta->get("Formatter")->get("format"));
    }

    abstract protected function getEncoder();

    // functions for compact mode

    private $idx;

    private $keyPrefix = "#e#";

    private $compactEntityList = [];

    /**
     * {@inheritdoc}
     */
    public function compactEntities($response)
    {
        $this->idx = 0;
        $this->compactEntityList = [];

        $payload = $this->processValue($response);
        $compactEntityList = [];

        foreach ($this->compactEntityList as $compactEntity)
            $compactEntityList[$compactEntity["idx"]] = $compactEntity["obj"];

        return [$payload, $compactEntityList];
    }

    public function processValue($value)
    {
        $processed = null;

        if (is_scalar($value))
        {
            $processed = $value;
        }
        elseif (is_array($value))
        {
            $processed = [];
            foreach ($value as $k=>$v)
                $processed[$k] = $this->processValue($v);
        }
        elseif (is_object($value))
        {
            if ($this->isEntityObject($value))
            {
                $processed = $this->addEntityObject($value);
            }
            else
            {
                $processed = new \stdClass();
                foreach (get_object_vars($value) as $k=>$v)
                    $processed->$k = $this->processValue($v);
            }
        }

        return $processed;
    }

    private function isEntityObject($value)
    {
        return (is_callable([$value, "has"]) &&
            $value->has("id") &&
            $value->get("id"));
    }

    private function addEntityObject($object)
    {
        $key = sprintf("%s:%s", $object->getObjectName(), $object->get("id"));

        if (!isset($this->compactEntityList[$key]))
        {
            $this->compactEntityList[$key] = [
                "idx" => sprintf("%s:%s", $this->keyPrefix, $this->idx++),
                "obj" => new \stdClass()
            ];

            foreach ($object->getValues() as $k => $v)
                $this->compactEntityList[$key]["obj"]->$k = $this->processValue($v);
        }

        return $this->compactEntityList[$key]["idx"];
    }
}
