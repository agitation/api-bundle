<?php
declare(strict_types=1);

/*
 * @package    agitation/api-bundle
 * @link       http://github.com/agitation/api-bundle
 * @author     Alexander Günsche
 * @license    http://opensource.org/licenses/MIT
 */

namespace Agit\ApiBundle\Service;

use Agit\ApiBundle\Annotation\Annotation;
use Agit\ApiBundle\Annotation\Depends;
use Agit\ApiBundle\Annotation\Object\Object;
use Agit\ApiBundle\Annotation\Property\AbstractPropertyMeta;
use Agit\ApiBundle\Annotation\Property\AbstractType;
use Agit\ApiBundle\Annotation\Property\Name;
use Agit\ApiBundle\Annotation\Property\ObjectType;
use Agit\BaseBundle\Exception\InternalErrorException;
use Agit\BaseBundle\Tool\StringHelper;
use Doctrine\Common\Annotations\Reader;
use Doctrine\Common\Cache\Cache;
use ReflectionClass;
use Symfony\Component\HttpKernel\Kernel;

class ObjectProcessor extends AbstractProcessor
{
    protected $kernel;

    protected $classCollector;

    protected $cacheProvider;

    protected $annotationReader;

    private $cnt = [];

    public function __construct(Kernel $kernel, ClassCollector $classCollector, Reader $annotationReader, Cache $cacheProvider)
    {
        $this->kernel = $kernel;
        $this->classCollector = $classCollector;
        $this->cacheProvider = $cacheProvider;
        $this->annotationReader = $annotationReader;
    }

    public function process()
    {
        $this->collect(Object::class, 'agit.api.object');
    }

    protected function processClass(ReflectionClass $classRefl, Annotation $desc, array $classAnnotations)
    {
        $class = $classRefl->getName();
        $namespace = $desc->get('namespace');
        $allDefaults = $classRefl->getDefaultProperties();
        $defaults = [];

        if (! $namespace)
        {
            throw new InternalErrorException(sprintf("ATTENTION: missing namespace on %s\n", $class));
        }

        $objectName = "$namespace/" . $classRefl->getShortName();
        $objectMeta = [];
        $propMetaList = [];

        $desc->set('objectName', $objectName);
        $objectMeta['Object'] = $desc;
        $objectMeta['Name'] = isset($classAnnotations[Name::class]) ? $classAnnotations[Name::class] : new Name(['value' => $objectName]);
        $deps = isset($classAnnotations[Depends::class]) ? $classAnnotations[Depends::class] : new Depends();

        foreach ($classRefl->getProperties() as $propertyRefl)
        {
            $annotations = $this->annotationReader->getPropertyAnnotations($propertyRefl);
            $propName = $propertyRefl->getName();
            $propMeta = [];

            foreach ($annotations as $annotation)
            {
                if (! ($annotation instanceof AbstractPropertyMeta))
                {
                    continue;
                }

                $propMetaClass = StringHelper::getBareClassName($annotation);
                $propMetaName = ($annotation instanceof AbstractType) ? 'Type' : $propMetaClass;
                $propMeta[$propMetaName] = $annotation;
            }

            if (! isset($propMeta['Type']))
            {
                continue;
            }

            $defaults[$propName] = $allDefaults[$propName];

            if ($propMeta['Type']->get('readonly'))
            {
                $defaults[$propName] = null;
            }
            elseif ($propMeta['Type']->isListType() && ! is_array($defaults[$propName]))
            {
                $defaults[$propName] = [];
            }

            if ($propMeta['Type'] instanceof ObjectType)
            {
                $targetClass = $propMeta['Type']->get('class');

                if ($targetClass === null)
                {
                    throw new InternalErrorException("Error in $objectName, property $propName: The target class must be specified.");
                }

                $propMeta['Type']->set('class', $this->fixObjectName($namespace, $targetClass));
            }

            if (! isset($propMeta['Name']) || ! $propMeta['Name']->get('value'))
            {
                $propMeta['Name'] = new Name(['value' => $propName]);
            }

            $propMetaList[$propName] = $this->dissectMetaList($propMeta);
        }

        $this->addEntry($objectName, [
            'class' => $classRefl->getName(),
            'deps' => $this->dissectMeta($deps),
            'objectMeta' => $this->dissectMetaList($objectMeta),
            'defaults' => $defaults,
            'propMetaList' => $propMetaList
        ]);
    }
}
