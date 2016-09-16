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
        $compactHeader = $httpRequest->headers->get("x-api-serialize-compact", null, true);
        $compactHeader = "true";

        if ($compactHeader === "true") {
            list($payload, $entityList) = $this->compactEntities($result);

            // overhead is only worth it if there are significantly more references than entities
            if ($this->rNum * .8 > $this->eNum) {
                $result = $this->objectMetaService->createObject("common.v1/Response");
                $result->setPayload($payload);
                $result->setEntityList($entityList);
            }
        }

        return $this->encode($result);
    }

    abstract protected function encode($result);

    // functions for compact mode

    private $idx;

    private $keyPrefix = "#e#";

    private $compactEntityList = [];

    private $rNum = 0;

    private $eNum = 0;

    /**
     * {@inheritdoc}
     */
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
        ++$this->eNum;

        if (! isset($this->compactEntityList[$key])) {
            ++$this->rNum;
            $this->compactEntityList[$key] = [
                "idx" => sprintf("%s:%s", $this->keyPrefix, $this->idx++),
                "obj" => new stdClass()
            ];

            foreach ($object->getValues() as $k => $v) {
                $this->compactEntityList[$key]["obj"]->$k = $this->processValue($v);
            }
        }

        return $this->compactEntityList[$key]["idx"];
    }
}
