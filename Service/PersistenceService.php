<?php
/**
 * @package    agitation/api
 * @link       http://github.com/agitation/AgitApiBundle
 * @author     Alex Günsche <http://www.agitsol.com/>
 * @copyright  2012-2015 AGITsol GmbH
 * @license    http://opensource.org/licenses/MIT
 */

namespace Agit\ApiBundle\Service;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\ClassMetadataInfo;
use Doctrine\ORM\Proxy\Proxy;
use Doctrine\Common\Collections\Collection;
use Agit\CommonBundle\Exception\InternalErrorException;
use Agit\ApiBundle\Common\AbstractObject;

class PersistenceService
{
    protected $entityManager;

    public function __construct(EntityManager $entityManager, ObjectService $objectService)
    {
        $this->entityManager = $entityManager;
        $this->objectService = $objectService;
    }

    public function saveEntity($entity, AbstractObject $object)
    {
        try
        {
            $this->entityManager->beginTransaction();

            $this->fillEntity($entity, $object);

            $this->entityManager->flush();
            $this->entityManager->commit();

            $this->entityManager->refresh($entity);
        }
        catch (\Exception $e)
        {
            $this->entityManager->rollback();
            throw $e;
        }
    }

    private function fillEntity($entity, AbstractObject $object)
    {
        if ($entity instanceof Proxy)
            $entity->__load();

        $entityMeta = $this->entityManager->getClassMetadata(get_class($entity));
        $assoc = $entityMeta->getAssociationNames();
        $allFields = array_merge($assoc, $entityMeta->getFieldNames());

        foreach ($allFields as $prop)
        {
            if (!$object->has($prop) || $entityMeta->isIdentifier($prop))
                continue;

            $type = $object->getPropertyMeta($prop, "Type");
            $setter = "set" . ucfirst($prop);

            if ($type->get("readonly") || !is_callable([$entity, $setter]))
                continue;

            $value = $object->get($prop);

            if (!in_array($prop, $assoc))
            {
                $entity->$setter($value);
            }
            else
            {
                $mapping = $entityMeta->getAssociationMapping($prop);
                $targetEntity = $mapping["targetEntity"];
                $isOwning = $mapping["isOwningSide"];

                if ($mapping["type"] & ClassMetadataInfo::TO_ONE)
                {
                    // TODO: If is not owning: create a new entity and pass it to the setter

                    if ($type->isListType())
                        throw new InternalErrorException("Mismatch between object and entity: The `$prop` property is a list, while the entity field is a xToOne relation.");

                    if ($isOwning)
                    {
                        // ONE_TO_ONE or MANY_TO_ONE

                        if (!is_scalar($value) && !is_null($value))
                            throw new InternalErrorException("Bad object value: The `$prop` property must be scalar or null.");

                        $ref = $this->entityManager->getReference($targetEntity, $value);
                        $entity->$setter($ref);
                    }
                    else
                    {
                        // ONE_TO_ONE

                        $childEntity = $entityMeta->getFieldValue($entity, $prop);

                        if (!$childEntity)
                            $childEntity = $this->entityManager->getClassMetadata($targetEntity)->newInstance();

                        $this->fillEntity($childEntity, $value);
                        $entity->$setter($childEntity);
                    }
                }
                elseif ($mapping["type"] & ClassMetadataInfo::TO_MANY)
                {
                    if (!$type->isListType() || !is_array($value))
                        throw new InternalErrorException("Mismatch between object and entity: The `$prop` property is a single object reference, while the entity field is a xToMany relation.");

                    $adder = "add" . ucfirst($prop);
                    $children = $entityMeta->getFieldValue($entity, $prop);

                    if (!($children instanceof Collection))
                        throw new InternalErrorException("Bad entity: The `$prop` property is expected to be a Doctrine collection.");

                    // re-assign as (indexed) array. Of course, we could search directly in the collection,
                    // but we need to keep track of obsolete children anyway, and this is easier with an array
                    $children = $children->toArray();

                    foreach ($value as $val)
                    {
                        // TODO: Remove obsolete children, or better: keep track of new/updated/removed children

                        if ($isOwning)
                        {
                            // MANY_TO_MANY

                            $ref = $this->entityManager->getReference($targetEntity, $val);
                            $entity->$adder($ref);
                        }
                        else
                        {
                            // ONE_TO_MANY or MANY_TO_MANY

                            $id = $value->get("id");

                            if (isset($children[$id]))
                            {
                                $childEntity = $children[$id];
                                unset($children[$id]);
                            }
                            else
                            {
                                $childEntity = $this->entityManager->getClassMetadata($targetEntity)->newInstance();
                            }

                            $this->fillEntity($childEntity, $value);
                        }
                    }

                    // remove obsolete children
                    if ($mapping["type"] & ClassMetadataInfo::ONE_TO_MANY)
                        foreach ($children as $child)
                            $this->entityManager->remove($child);
                }
            }
        }

        $this->entityManager->persist($entity);
    }
}
