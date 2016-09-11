<?php

/*
 * @package    agitation/api-bundle
 * @link       http://github.com/agitation/api-bundle
 * @author     Alexander Günsche
 * @license    http://opensource.org/licenses/MIT
 */

namespace Agit\ApiBundle\Plugin\ApiFormatter;

use Agit\BaseBundle\Pluggable\Depends;
use Agit\BaseBundle\Pluggable\ServiceAwarePluginInterface;
use Agit\BaseBundle\Pluggable\ServiceAwarePluginTrait;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

/**
 * @Depends({"@agit.api.objectmeta"})
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
        $response = $this->controller->getResponse();

        if ($compactHeader === "true") {
            list($payload, $entityList) = $this->compactEntities($response);

            $response = $this->getService("agit.api.objectmeta")->createObject("common.v1/Response");
            $response->setPayload($payload);
            $response->setEntityList($entityList);
        }

        return $this->encode($response);
    }

    abstract protected function encode($response);

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

        foreach ($this->compactEntityList as $compactEntity) {
            $compactEntityList[$compactEntity["idx"]] = $compactEntity["obj"];
        }

        return [$payload, $compactEntityList];
    }

    public function processValue($value)
    {
        $processed = null;

        if (is_scalar($value)) {
            $processed = $value;
        } elseif (is_array($value)) {
            $processed = [];
            foreach ($value as $k => $v) {
                $processed[$k] = $this->processValue($v);
            }
        } elseif (is_object($value)) {
            if ($this->isEntityObject($value)) {
                $processed = $this->addEntityObject($value);
            } else {
                $processed = new \stdClass();
                foreach (get_object_vars($value) as $k => $v) {
                    $processed->$k = $this->processValue($v);
                }
            }
        }

        return $processed;
    }

    private function isEntityObject($value)
    {
        return is_callable([$value, "has"]) &&
            $value->has("id") &&
            $value->get("id");
    }

    private function addEntityObject($object)
    {
        $key = sprintf("%s:%s", $object->getObjectName(), $object->get("id"));

        if (! isset($this->compactEntityList[$key])) {
            $this->compactEntityList[$key] = [
                "idx" => sprintf("%s:%s", $this->keyPrefix, $this->idx++),
                "obj" => new \stdClass()
            ];

            foreach ($object->getValues() as $k => $v) {
                $this->compactEntityList[$key]["obj"]->$k = $this->processValue($v);
            }
        }

        return $this->compactEntityList[$key]["idx"];
    }
}
