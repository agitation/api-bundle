<?php
/**
 * @package    agitation/api
 * @link       http://github.com/agitation/AgitApiBundle
 * @author     Alex Günsche <http://www.agitsol.com/>
 * @copyright  2012-2015 AGITsol GmbH
 * @license    http://opensource.org/licenses/MIT
 */

namespace Agit\ApiBundle\Plugin;

use Doctrine\Common\Annotations\Reader;
use Agit\CommonBundle\Helper\StringHelper;
use Agit\ApiBundle\Annotation\AbstractMeta;
use Agit\ApiBundle\Annotation\Object\Object;
use Agit\ApiBundle\Annotation\Property\AbstractType;
use Agit\ApiBundle\Annotation\Property\ObjectType;
use Agit\ApiBundle\Annotation\Property\Name;
use Agit\CommonBundle\Exception\InternalErrorException;

abstract class AbstractApiObjectPlugin extends AbstractApiPlugin
{
    // API namespace, to be provided by the plugin
    abstract protected function getApiNamespace();

    final protected function getType()
    {
        return 'object';
    }

    final protected function getBaseClass()
    {
        return 'Agit\ApiBundle\Plugin\Api\Object\AbstractObject';
    }

    final protected function process(\ReflectionClass $classRefl)
    {
        $objectMeta = [];
        $propMetaList = [];
        $objectName = sprintf("%s/%s", $this->getApiNamespace(), $classRefl->getShortName());

        $objAnnotationList = $this->getService('annotation_reader')->getClassAnnotations($classRefl);

        foreach ($objAnnotationList as $annotation)
        {
            if (!($annotation instanceof AbstractMeta))
                continue;

            $objMetaName = StringHelper::getBareClassName($annotation);
            $objectMeta[$objMetaName] = $annotation;
        }

        if (isset($objectMeta['Object']))
            $objectMeta['Object']->set('objectName', $objectName);
        else
            $objectMeta['Object'] = new Object(['objectName' => $objectName]);

        foreach ($classRefl->getProperties() as $propertyRefl)
        {
            $annotationList = $this->getService('annotation_reader')->getPropertyAnnotations($propertyRefl);
            $propName = $propertyRefl->getName();
            $propMeta = [];

            foreach ($annotationList as $annotation)
            {
                if (!($annotation instanceof AbstractMeta))
                    continue;

                $propMetaClass = StringHelper::getBareClassName($annotation);
                $propMetaName = ($annotation instanceof AbstractType) ? 'Type' : $propMetaClass;
                $propMeta[$propMetaName] = $annotation;
            }

            if (!isset($propMeta['Type']))
                continue;

            $propMeta['Type']->setReference($this->getApiNamespace(), $classRefl->getShortName(), $propName);

            if ($propMeta['Type'] instanceof ObjectType)
                $propMeta['Type']->set('class', $this->fixObjectName($propMeta['Type']->get('class')));

            if (!isset($propMeta['Name']) || !$propMeta['Name']->get('value'))
                $propMeta['Name'] = new Name(['value' => $propName]);

            $propMetaList[$propName] = $this->dissectMetaList($propMeta);
        }

        if ($objectMeta['Object']->get('isScalar') && (count($propMetaList) !== 1 || !isset($propMetaList['value'])))
            throw new InternalErrorException("Scalar objects must contain only a 'value' property.");

        $this->registerEntry($objectName, [
            'class' => $classRefl->getName(),
            'objectMeta' => $this->dissectMetaList($objectMeta),
            'propMetaList' => $propMetaList
        ]);
    }
}
