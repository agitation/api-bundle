<?php

/*
 * @package    agitation/api-bundle
 * @link       http://github.com/agitation/api-bundle
 * @author     Alexander Günsche
 * @license    http://opensource.org/licenses/MIT
 */

namespace Agit\ApiBundle\Service;

use stdClass;
use Symfony\Component\HttpFoundation\Request;

abstract class AbstractSerializableFormatter extends AbstractFormatter
{
    protected $objectMetaService;

    protected $debug;

    public function __construct(ObjectMetaService $objectMetaService, $debug)
    {
        $this->objectMetaService = $objectMetaService;
        $this->debug = $debug;
    }

    protected function getHttpContent(Request $httpRequest, $result)
    {
        if (! $this->debug && $httpRequest->headers->get("x-api-serialize-compact", null, true) === "true") {
            list($payload, $entityList) = $this->compactEntities($result);
            $result = $this->objectMetaService->createObject("common.v1/Response");
            $result->setPayload($payload);
            $result->setEntityList($entityList);
        }

        return $this->encode($result);
    }

    abstract protected function encode($result);

    // functions for compact mode

    private $idx;

    private $keyPrefix = "#e#";

    private $compactEntityList = [];

    public function compactEntities($result)
    {
        $this->idx = 0;
        $this->compactEntityList = [];

        $payload = $this->processValue($result);
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
                $values = get_object_vars($value);
                $processed = $values ? [] : new stdClass(); // create stdClass only if values are empty, otherwise an assoc array will do

                foreach ($values as $k => $v) {
                    $processed[$k] = $this->processValue($v);
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
                "obj" => [] // associative arrays are faster than stdClass and have the same effect
            ];

            foreach ($object->getValues() as $k => $v) {
                $this->compactEntityList[$key]["obj"][$k] = $this->processValue($v);
            }
        }

        return $this->compactEntityList[$key]["idx"];
    }
}
