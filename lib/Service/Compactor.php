<?php

/*
 * @package    agitation/api-bundle
 * @link       http://github.com/agitation/api-bundle
 * @author     Alexander Günsche
 * @license    http://opensource.org/licenses/MIT
 */

namespace Agit\ApiBundle\Service;

use Agit\ApiBundle\Api\Object\EntityObjectInterface;
use Agit\ApiBundle\Api\Object\ObjectInterface;
use stdClass;

class Compactor
{
    const KEY_PREFIX = "#e#";

    private $idx = 0;

    private $tmpEntityList = [];

    private $payload;

    private $entityList = [];

    /**
     * @param ObjectInterface|array $tree
     */
    public function __construct($tree)
    {
        $this->payload = $this->process($tree);

        foreach ($this->tmpEntityList as $compactEntity) {
            $this->entityList[$compactEntity["idx"]] = $compactEntity["obj"];
        }
    }

    public function getPayload()
    {
        return $this->payload;
    }

    public function getEntities()
    {
        return $this->entityList;
    }

    private function process($value)
    {
        $processed = null;

        if (is_scalar($value)) {
            $processed = $value;
        } elseif (is_array($value)) {
            $processed = [];
            foreach ($value as $k => $v) {
                $processed[$k] = $this->process($v);
            }
        } elseif (is_object($value)) {
            if ($value instanceof EntityObjectInterface) {
                $processed = $this->addEntityObject($value);
            } else {
                $values = ($value instanceof ObjectInterface)
                    ? $value->getValues()
                    : get_object_vars($value);

                $processed = $values ? [] : new stdClass(); // create stdClass only if values are empty, otherwise an assoc array will do

                foreach ($values as $k => $v) {
                    $processed[$k] = $this->process($v);
                }
            }
        }

        return $processed;
    }

    private function addEntityObject(EntityObjectInterface $object)
    {
        $key = sprintf("%s:%s", $object->getObjectName(), $object->getId());

        if (! isset($this->tmpEntityList[$key])) {
            $this->tmpEntityList[$key] = [
                "idx" => sprintf("%s:%s", self::KEY_PREFIX, $this->idx++),
                "obj" => [] // associative arrays are faster than stdClass and have the same effect
            ];

            foreach ($object->getValues() as $k => $v) {
                $this->tmpEntityList[$key]["obj"][$k] = $this->process($v);
            }
        }

        return $this->tmpEntityList[$key]["idx"];
    }
}
