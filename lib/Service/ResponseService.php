<?php

/*
 * @package    agitation/api-bundle
 * @link       http://github.com/agitation/api-bundle
 * @author     Alexander Günsche
 * @license    http://opensource.org/licenses/MIT
 */

namespace Agit\ApiBundle\Service;

use Agit\ApiBundle\Api\Object\AbstractObject;
use Agit\ApiBundle\Api\Object\ResponseObjectInterface;
use Agit\BaseBundle\Exception\InternalErrorException;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\ClassMetadataInfo;
use Doctrine\ORM\Proxy\Proxy;

class ResponseService extends AbstractObjectService
{
    private $entityManager;

    // cache of entity classes
    private $entities;

    // cache of generated objects with an identity
    private $objects = [];

    private $view;

    public function __construct(ObjectMetaService $objectMetaService, EntityManager $entityManager)
    {
        parent::__construct($objectMetaService);
        $this->entityManager = $entityManager;
    }

    public function setView($view)
    {
        $this->view = $view;
    }

    public function createResponseObject($objectName, $data = null)
    {
        if (! $object = $this->cacheGet($objectName, $data)) {
            $object = $this->objectMetaService->createObject($objectName);

            if (! ($object instanceof ResponseObjectInterface)) {
                throw new InternalErrorException("Object $objectName must implement ResponseObjectInterface!");
            }

            $object->setResponseService($this);

            $object->fill($data);
            $this->cachePut($object);
        }

        return $object;
    }

    protected function fill(AbstractObject $object, $data)
    {
        // allows the object to handle the data itself; it'll use $this as fallback.
        $object->fill($data);
    }

    public function fillObjectFromPlain(ResponseObjectInterface $object, $data)
    {
        if ($object instanceof DataAwareResponseObjectInterface) {
            $object->fill($data);
        } elseif ($this->isEntity($data)) {
            $object = $this->fillObjectFromEntity($object, $data);
        } elseif (is_object($data)) {
            $values = get_object_vars($data) + $object->getValues();
            $metas = $this->objectMetaService->getObjectPropertyMetas($object->getObjectName());

            foreach ($values as $propName => $value) {
                if (isset($metas[$propName])) {
                    if ($metas[$propName]->has("View") && ! $this->doInclude($metas[$propName]->get("View"), $propName)) {
                        unset($object->$propName);
                        continue;
                    }

                    $object->set($propName, $this->createFieldValue($metas[$propName]->get("Type"), $propName, $value));
                }
            }
        }

        return $object;
    }

    public function fillObjectFromEntity(ResponseObjectInterface $object, $entity)
    {
        $metadata = $this->entityManager->getClassMetadata(get_class($entity));

        if ($entity instanceof Proxy) {
            $entity->__load();
        }

        foreach ($object->getKeys() as $propName) {
            $metas = $this->getPropertyMetas($object, $propName);

            if (! $this->doInclude($metas["View"], $propName)) {
                unset($object->$propName);
                continue;
            }

            $getter = "get" . ucfirst($propName);
            $value = null;

            // check if a getter exists, otherwise access value through metadata
            if (is_callable([$entity, $getter])) {
                $value = $entity->$getter();
            } elseif ($metadata->hasField($propName) || $metadata->hasAssociation($propName)) {
                $value = $metadata->getFieldValue($entity, $propName);
            }

            if ($metadata->hasField($propName)) {
                $object->set($propName, $value);
            } elseif ($metadata->hasAssociation($propName)) {
                $mapping = $metadata->getAssociationMapping($propName);
                $typeMeta = $metas["Type"];

                if (! $typeMeta->isObjectType() && ! $typeMeta->isEntityType()) {
                    throw new InternalErrorException(sprintf("Wrong type for the `%s` field of the `%s` object: Must be an object/entity type.", $propName, $object->getObjectName()));
                }

                if ($mapping["type"] & ClassMetadataInfo::TO_ONE) {
                    if ($value) {
                        $object->set($propName, $this->createResponseObject($typeMeta->getTargetClass(), $value));
                    }
                } elseif ($mapping["type"] & ClassMetadataInfo::TO_MANY) {
                    if (! $typeMeta->isListType()) {
                        throw new InternalErrorException(sprintf("Wrong type for the `%s` field of the `%s` object: Must be a list type.", $propName, $object->getObjectName()));
                    }

                    $values = $value->getValues();

                    if (count($values)) {
                        foreach ($value->getValues() as $val) {
                            $object->add($propName, $this->createResponseObject($typeMeta->getTargetClass(), $val));
                        }
                    }
                }
            }
        }
    }

    public function isEntity($data)
    {
        $isEntity = false;

        if (is_object($data)) {
            if (! $this->entities) {
                $this->entities = $this->entityManager->getConfiguration()
                    ->getMetadataDriverImpl()->getAllClassNames();
            }

            $className = get_class($data);

            if ($data instanceof Proxy) {
                $className = get_parent_class($data);
            }

            $isEntity = in_array($className, $this->entities);
        }

        return $isEntity;
    }

    private function getPropertyMetas($object, $propName)
    {
        $metas = $this->objectMetaService->getPropertyMetas($object->getObjectName(), $propName);

        return [
            "Type" => $metas->get("Type"),
            "View" => $metas->has("View") ? $metas->get("View") : null
        ];
    }

    private function doInclude($viewMeta, $propName)
    {
        return
            ! $viewMeta ||
            in_array($this->view, $viewMeta->get("only")) ||
            (! $viewMeta->get("only") && ! in_array($this->view, $viewMeta->get("not")));
    }
    private function cacheGet($objectName, $data)
    {
        $id = $object = null;

        if ($this->isEntity($data) && method_exists($data, "getId")) {
            $id = $data->getId();
        } elseif ($data instanceof AbstractObject && $data->has("id")) {
            $id = $data->get("id");
        }

        if ($id) {
            $key = "$objectName:$id";

            if (isset($this->objects[$key])) {
                $object = $this->objects[$key];
            }
        }

        return $object;
    }

    private function cachePut($object)
    {
        if ($object instanceof AbstractObject && $object->has("id") && $object->get("id")) {
            $key = sprintf("%s:%s", $object->getObjectName(), $object->get("id"));
            $this->objects[$key] = $object;
        }
    }
}
