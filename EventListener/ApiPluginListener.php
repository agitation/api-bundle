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
use Agit\CoreBundle\Exception\InternalErrorException;
use Agit\CoreBundle\Service\ClassCollector;
use Agit\CoreBundle\Pluggable\Strategy\Cache\CacheRegistrationEvent;
use Agit\CoreBundle\Helper\StringHelper;
use Agit\IntlBundle\Service\Translate;
use Agit\ApiBundle\Service\ObjectService;
use Agit\ApiBundle\Api\Meta\AbstractMeta;
use Agit\ApiBundle\Api\Meta\Property\AbstractType;
use Agit\ApiBundle\Api\Meta\Property\ObjectType;
use Agit\ApiBundle\Api\Meta\Property\Name;
use Agit\ApiBundle\Api\Meta\Object\Object;

/**
 * Reusable listener that collects plugin objects from a given path. "Reusable"
 * means that you can use an instance of this listener as service, without the
 * need of creating a derived class or own implementation.
 */
class ApiPluginListener
{
    private $ClassCollector;

    private $namespace;

    private $searchPath;

    private $type;

    private $parentClass;

    private $priority;

    private $RegistrationEvent;

    public function __construct(Reader $AnnotationReader, ClassCollector $ClassCollector, $type, $parentClass, $namespace, $searchPath, $priority)
    {
        $this->AnnotationReader = $AnnotationReader;
        $this->ClassCollector = $ClassCollector;
        $this->Translate = new Translate();
        $this->type = $type;
        $this->parentClass = $parentClass;
        $this->namespace = $namespace;
        $this->searchPath = $searchPath;
        $this->priority = $priority;
    }

    /**
     * the event listener to be used in the service configuration
     */
    public function onRegistration(CacheRegistrationEvent $RegistrationEvent)
    {
        $this->RegistrationEvent = $RegistrationEvent;

        foreach ($this->ClassCollector->collect($this->searchPath) as $class)
        {
            $ClassRefl = new \ReflectionClass($class);

            if (!$ClassRefl->isSubclassOf($this->parentClass))
                continue;

            if ($this->type === 'endpoint')
                $this->processEndpoint($ClassRefl);
            elseif ($this->type === 'object')
                $this->processObject($ClassRefl);
            elseif ($this->type === 'formatter')
                $this->processFormatter($ClassRefl);
            else
                throw new InternalErrorException("Invalid API plugin type: {$this->type}.");
        }
    }

    private function registerEntry($key, $value)
    {
        $CacheData = $this->RegistrationEvent->createContainer();
        $CacheData->setId($key);
        $CacheData->setData($value);
        $this->RegistrationEvent->register($CacheData, $this->priority);
    }

    private function processEndpoint($ClassRefl)
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

            $callMeta['Call']->setReference($this->namespace, $ClassRefl->getShortName(), $MethodRefl->getName());

            $endpointCall = sprintf(
                "%s/%s.%s",
                $this->namespace,
                $ClassRefl->getShortName(),
                $MethodRefl->getName());

            $this->registerEntry($endpointCall, [
                'class' => $ClassRefl->getName(),
                'meta' => $this->dissectMetaList($callMeta)
            ]);
        }
    }

    private function processObject($ClassRefl)
    {
        $objectMeta = [];
        $propMetaList = [];
        $objectName = sprintf("%s/%s", $this->namespace, $ClassRefl->getShortName());

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

            $propMeta['Type']->setReference($this->namespace, $ClassRefl->getShortName(), $propName);


            if ($propMeta['Type'] instanceof ObjectType)
                $propMeta['Type']->set('class', $this->fixObjectName($propMeta['Type']->get('class')));

            if (isset($propMeta['Name']) && $propMeta['Name']->getName())
                $propMeta['Name']->setName($this->Translate->t($propMeta['Name']->getName()));
            else
                $propMeta['Name'] = new Name(['value' => $propName]);

            $propMetaList[$propName] = $this->dissectMetaList($propMeta);
        }

        $this->registerEntry($objectName, [
            'class' => $ClassRefl->getName(),
            'objectMeta' => $this->dissectMetaList($objectMeta),
            'propMetaList' => $propMetaList
        ]);
    }

    private function processFormatter($ClassRefl)
    {
        $properties = $ClassRefl->getStaticProperties();
        $this->registerEntry($properties['format'], $ClassRefl->getName());
    }

    private function dissectMetaList($MetaList)
    {
        $newList = [];

        // While we could store the Meta objects as they are,
        // we'd have to unserialize hundreds of them on every API access.
        // Therefore we store class names and options separately.

        foreach ($MetaList as $name => $Meta)
            $newList[$name] = ['class' => get_class($Meta), 'options' => $Meta->getOptions()];

        return $newList;
    }

    private function fixObjectName($name)
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
            $parts['namespace'] = $this->namespace;
            $parts['class'] = $name;
        }

        return implode('/', $parts);
    }

}
