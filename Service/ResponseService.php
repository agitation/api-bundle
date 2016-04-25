<?php
/**
 * @package    agitation/api
 * @link       http://github.com/agitation/AgitApiBundle
 * @author     Alex Günsche <http://www.agitsol.com/>
 * @copyright  2012-2015 AGITsol GmbH
 * @license    http://opensource.org/licenses/MIT
 */

namespace Agit\ApiBundle\Service;


use Doctrine\ORM\Mapping\ClassMetadataInfo;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Proxy\Proxy;
use Agit\CommonBundle\Exception\InternalErrorException;
use Agit\IntlBundle\Translate;
use Agit\ApiBundle\Common\AbstractObject;
use Agit\ApiBundle\Common\AbstractEntityObject;
use Agit\ApiBundle\Common\DataAwareResponseObjectInterface;

class ResponseService extends AbstractObjectService
{
    private $entityManager;

    // cache of entity classes
    private $entities;

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
        $object = $this->objectMetaService->createObject($objectName);
        $this->fill($object, $data);

        return $object;
    }

    public function fill(AbstractObject $object, $data)
    {
        if ($object instanceof DataAwareResponseObjectInterface)
        {
            $object->fill($data);
        }
        elseif ($this->isEntity($data))
        {
            $object = $this->fillObjectFromEntity($object, $data);
        }
        elseif (is_object($data))
        {
            $values = get_object_vars($data) + $object->getValues();

            foreach ($values as $propName => $value)
            {
                $metas = $this->getPropertyMetas($object, $propName);

                if (!$this->doInclude($metas["View"], $propName))
                    continue;

                $object->set($propName, $this->createFieldValue($metas["Type"], $propName, $value));
            }
        }

        return $object;
    }

    public function fillObjectFromEntity($object, $entity)
    {
        $metadata = $this->entityManager->getClassMetadata(get_class($entity));

        if ($entity instanceof Proxy)
            $entity->__load();

        if ($object instanceof AbstractEntityObject)
            $object->setEntity($entity);

        foreach (array_keys($object->getValues()) as $propName)
        {
            $metas = $this->getPropertyMetas($object, $propName);

            if (!$this->doInclude($metas["View"], $propName))
                continue;

            $getter = "get" . ucfirst($propName);
            $value = null;

            // check if a getter exists, otherwise access value through metadata
            if (is_callable([$entity, $getter]))
                $value = $entity->$getter();
            elseif ($metadata->hasField($propName) || $metadata->hasAssociation($propName))
                $value = $metadata->getFieldValue($entity, $propName);

            if ($metadata->hasField($propName))
            {
                $object->set($propName, $value);
            }
            elseif ($metadata->hasAssociation($propName))
            {
                $mapping = $metadata->getAssociationMapping($propName);
                $typeMeta = $metas["Type"];

                if (!$typeMeta->isObjectType() && !$typeMeta->isEntityType())
                    throw new InternalErrorException(sprintf("Wrong type for the `%s` field of the `%s` object: Must be an object/entity type.", $propName, $object->getObjectName()));

                if ($mapping["type"] & ClassMetadataInfo::TO_ONE)
                {
                    $object->set($propName, $this->createResponseObject($typeMeta->getTargetClass(), $value));
                }
                elseif ($mapping["type"] & ClassMetadataInfo::TO_MANY)
                {
                    if (!$typeMeta->isListType())
                        throw new InternalErrorException(sprintf("Wrong type for the `%s` field of the `%s` object: Must be a list type.", $propName, $object->getObjectName()));

                    foreach ($value->getValues() as $val)
                        $object->add($propName, $this->createResponseObject($typeMeta->getTargetClass(), $val));
                }
            }
        }
    }

    public function isEntity($data)
    {
        $isEntity = false;

        if (is_object($data))
        {
            if (!$this->entities)
                $this->entities = $this->entityManager->getConfiguration()
                    ->getMetadataDriverImpl()->getAllClassNames();

            $className = get_class($data);

            if ($data instanceof Proxy)
                $className = get_parent_class($data);

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
        return (
            !$viewMeta ||
            in_array($this->view, $viewMeta->get("only")) ||
            (!$viewMeta->get("only") && !in_array($this->view, $viewMeta->get("not")))
        );
    }
}
