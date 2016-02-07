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
use Agit\ApiBundle\Common\AbstractPersistableObject;

class PersistenceService
{
    protected $entityManager;

    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function saveEntity($entity, \stdClass $data)
    {
        try
        {
            $this->entityManager->beginTransaction();

            $this->fillEntity($entity, $data);

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

    private function fillEntity($entity, \stdClass $data)
    {
        if ($entity instanceof Proxy)
            $entity->__load();

        $entityClassName = get_class($entity);
        $entityMeta = $this->entityManager->getClassMetadata($entityClassName);
        $assoc = $entityMeta->getAssociationNames();
        $allFields = array_merge($assoc, $entityMeta->getFieldNames());

        foreach ($allFields as $prop)
        {
            if (!isset($data->$prop) || $entityMeta->isIdentifier($prop))
                continue;

            $value = $data->$prop;
            $setter = "set" . ucfirst($prop);

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
                    if (!is_array($value))
                        throw new InternalErrorException("Mismatch between object and entity: The `$prop` property is a single object reference, while the entity field is a xToMany relation.");

                    $children = $entityMeta->getFieldValue($entity, $prop);

                    if (!($children instanceof Collection))
                        throw new InternalErrorException("Bad entity: The `$prop` property is expected to be a Doctrine collection.");

                    $childrenArray = [];

                    // can't use the toArray() method, because we want the array to be indexed by the childrens' keys
                    foreach ($children as $child)
                        $childrenArray[$child->getId()] = $child;

                    foreach ($value as $childValue)
                    {
                        if ($isOwning)
                        {
                            // MANY_TO_MANY

                            if (!isset($childrenArray[$childValue]))
                            {
                                $ref = $this->entityManager->getReference($targetEntity, $childValue);
                                $children->add($ref);
                            }
                            else
                            {
                                // no action needed, just make sure it doesn't get deleted
                                unset($childrenArray[$childValue]);
                            }
                        }
                        else
                        {
                            // ONE_TO_MANY or MANY_TO_MANY

                            if (!is_object($childValue) || !property_exists($childValue, "id"))
                                throw new InternalErrorException("Bad child entity: expecting an object with an ID.");

                            $id = $childValue->id;

                            if (!is_null($id) && !isset($childrenArray[$id]))
                                throw new InternalErrorException("A child entity of $prop with ID $id doesn’t exist.");

                            if (isset($childrenArray[$id]))
                            {
                                $child = $childrenArray[$id];
                                unset($childrenArray[$id]);
                            }
                            else
                            {
                                $childMeta = $this->entityManager->getClassMetadata($targetEntity);
                                $childClassName = $childMeta->getName();
                                $child = new $childClassName();
                                $parentField = null;

                                foreach ($childMeta->getAssociationMappings() as $field => $childFieldMapping)
                                {
                                    if ($childFieldMapping["targetEntity"] === $entityClassName)
                                    {
                                        $parentField = $field;
                                        break;
                                    }
                                }

                                if (!$parentField)
                                    throw new InternalErrorException("Bad child entity: the child entity class is missing a relation to the parent.");

                                $childMeta->setFieldValue($child, $field, $entity);
                            }

                            $this->fillEntity($child, $childValue);

                            if (!$child->getId())
                                $children->add($child);
                        }
                    }

                    // remove obsolete children
                    foreach ($childrenArray as $child)
                        $children->removeElement($child);
                }
            }
        }

        $this->entityManager->persist($entity);
    }
}
