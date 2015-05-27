<?php
/**
 * @package    agitation/api
 * @link       http://github.com/agitation/AgitApiBundle
 * @author     Alex Günsche <http://www.agitsol.com/>
 * @copyright  2012-2015 AGITsol GmbH
 * @license    http://opensource.org/licenses/MIT
 */

namespace Agit\ApiBundle\EventListener;

use Doctrine\Common\Annotations\Reader;
use Agit\CoreBundle\Helper\StringHelper;
use Agit\ApiBundle\Api\Meta\AbstractMeta;
use Agit\ApiBundle\Api\Meta\Object\Object;
use Agit\ApiBundle\Api\Meta\Property\AbstractType;
use Agit\ApiBundle\Api\Meta\Property\ObjectType;
use Agit\ApiBundle\Api\Meta\Property\Name;
use Agit\CoreBundle\Exception\InternalErrorException;

/**
 * Listener skeleton containing the logic for processing endpoint, object and
 * formatter classes. To be inherited by actual implementations which collect
 * or otherwise know of existing API extensions.
 */
abstract class AbstractApiPluginListener
{
    protected $AnnotationReader;

    public function __construct(Reader $AnnotationReader)
    {
        $this->AnnotationReader = $AnnotationReader;
    }

    abstract protected function getRegistrationEvent();

    abstract protected function getNamespace();

    abstract protected function getPriority();

    protected function processEndpoint(\ReflectionClass $ClassRefl)
    {
        foreach ($ClassRefl->getMethods() as $MethodRefl)
        {
            $AnnotationList = $this->AnnotationReader->getMethodAnnotations($MethodRefl);
            $callMeta = [];

            foreach ($AnnotationList as $Annotation)
            {
                if (!($Annotation instanceof AbstractMeta))
                    continue;

                $callMetaName = StringHelper::getBareClassName(get_class($Annotation));
                $callMeta[$callMetaName] = $Annotation;
            }

            if (!isset($callMeta['Call']) || !isset($callMeta['Security']))
                continue;

            // fix implicit namespaces in request and response
            $callMeta['Call']->set('request', $this->fixObjectName($callMeta['Call']->get('request')));
            $callMeta['Call']->set('response', $this->fixObjectName($callMeta['Call']->get('response')));

            if ($callMeta['Call']->get('listobject'))
                $callMeta['Call']->set('listobject', $this->fixObjectName($callMeta['Call']->get('listobject')));

            $callMeta['Call']->setReference($this->getNamespace(), $ClassRefl->getShortName(), $MethodRefl->getName());

            $endpointCall = sprintf(
                "%s/%s.%s",
                $this->getNamespace(),
                $ClassRefl->getShortName(),
                $MethodRefl->getName());

            $this->registerEntry($endpointCall, [
                'class' => $ClassRefl->getName(),
                'meta' => $this->dissectMetaList($callMeta)
            ]);
        }
    }

    protected function processObject(\ReflectionClass $ClassRefl)
    {
        $objectMeta = [];
        $propMetaList = [];
        $objectName = sprintf("%s/%s", $this->getNamespace(), $ClassRefl->getShortName());

        $ObjAnnotationList = $this->AnnotationReader->getClassAnnotations($ClassRefl);

        foreach ($ObjAnnotationList as $Annotation)
        {
            if (!($Annotation instanceof AbstractMeta))
                continue;

            $objMetaName = StringHelper::getBareClassName($Annotation);
            $objectMeta[$objMetaName] = $Annotation;
        }

        if (isset($objectMeta['Object']))
            $objectMeta['Object']->set('objectName', $objectName);
        else
            $objectMeta['Object'] = new Object(['objectName' => $objectName]);

        foreach ($ClassRefl->getProperties() as $PropertyRefl)
        {
            $AnnotationList = $this->AnnotationReader->getPropertyAnnotations($PropertyRefl);
            $propName = $PropertyRefl->getName();
            $propMeta = [];

            foreach ($AnnotationList as $Annotation)
            {
                if (!($Annotation instanceof AbstractMeta))
                    continue;

                $propMetaClass = StringHelper::getBareClassName($Annotation);
                $propMetaName = ($Annotation instanceof AbstractType) ? 'Type' : $propMetaClass;
                $propMeta[$propMetaName] = $Annotation;
            }

            if (!isset($propMeta['Type']))
                continue;

            $propMeta['Type']->setReference($this->getNamespace(), $ClassRefl->getShortName(), $propName);

            if ($propMeta['Type'] instanceof ObjectType)
                $propMeta['Type']->set('class', $this->fixObjectName($propMeta['Type']->get('class')));

            if (!isset($propMeta['Name']) || !$propMeta['Name']->get('value'))
                $propMeta['Name'] = new Name(['value' => $propName]);

            $propMetaList[$propName] = $this->dissectMetaList($propMeta);
        }

        if ($objectMeta['Object']->get('isScalar') && (count($propMetaList) !== 1 || !isset($propMetaList['value'])))
            throw new InternalErrorException("Scalar objects must contain only a 'value' property.");

        $this->registerEntry($objectName, [
            'class' => $ClassRefl->getName(),
            'objectMeta' => $this->dissectMetaList($objectMeta),
            'propMetaList' => $propMetaList
        ]);
    }

    protected function processFormatter(\ReflectionClass $ClassRefl)
    {
        $properties = $ClassRefl->getStaticProperties();
        $this->registerEntry($properties['format'], $ClassRefl->getName());
    }

    protected function dissectMetaList($MetaList)
    {
        $newList = [];

        // While we could store the Meta objects as they are,
        // we'd have to unserialize hundreds of them on every API access.
        // Therefore we store class names and options separately.

        foreach ($MetaList as $name => $Meta)
            $newList[$name] = ['class' => get_class($Meta), 'options' => $Meta->getOptions()];

        return $newList;
    }

    protected function fixObjectName($name)
    {
        $parts = [];

        if (strpos($name, '/'))
        {
            $nameParts = explode('/', $name);
            $parts['namespace'] = $nameParts[0];
            $parts['class'] = $nameParts[1];
        }
        else
        {
            $parts['namespace'] = $this->getNamespace();
            $parts['class'] = $name;
        }

        return implode('/', $parts);
    }

    protected function registerEntry($key, $value)
    {
        $RegistrationEvent = $this->getRegistrationEvent();
        $CacheData = $RegistrationEvent->createContainer();
        $CacheData->setId($key);
        $CacheData->setData($value);
        $RegistrationEvent->register($CacheData, $this->getPriority());
    }
}
