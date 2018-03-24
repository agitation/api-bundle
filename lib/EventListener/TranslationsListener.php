<?php
declare(strict_types=1);

/*
 * @package    agitation/api-bundle
 * @link       http://github.com/agitation/api-bundle
 * @author     Alexander Günsche
 * @license    http://opensource.org/licenses/MIT
 */

namespace Agit\ApiBundle\EventListener;

use Agit\ApiBundle\Annotation\Property\Name;
use Agit\ApiBundle\Service\ClassCollector;
use Agit\IntlBundle\Event\BundleTranslationsEvent;
use Doctrine\Common\Annotations\Reader;
use Gettext\Translation;
use ReflectionClass;
use Symfony\Component\HttpKernel\KernelInterface;

class TranslationsListener
{
    private $kernel;

    private $classCollector;

    private $annotationReader;

    public function __construct(KernelInterface $kernel, ClassCollector $classCollector, Reader $annotationReader)
    {
        $this->kernel = $kernel;
        $this->classCollector = $classCollector;
        $this->annotationReader = $annotationReader;
    }

    public function onRegistration(BundleTranslationsEvent $event)
    {
        $bundleAlias = $event->getBundleAlias();
        $bundle = $this->kernel->getBundle($bundleAlias);
        $bundleNamespace = $bundle->getNamespace();
        $bundlePath = $bundle->getPath();

        if (! is_dir("$bundlePath/Api"))
        {
            return;
        }

        $classes = $this->classCollector->collect("$bundlePath/Api", false, false);

        foreach ($classes as $class)
        {
            $classRefl = new ReflectionClass($class);
            $fileLocation = '@' . str_replace($bundlePath, $bundleAlias, $classRefl->getFileName());

            $traitProps = $this->getTraitProperties($classRefl);

            foreach ($classRefl->getProperties() as $propRefl)
            {
                if ($propRefl->class !== $class || array_key_exists($propRefl->name, $traitProps))
                {
                    continue;
                }

                $name = $this->annotationReader->getPropertyAnnotation($propRefl, Name::class);

                if (! $name)
                {
                    continue;
                }

                $translation = new Translation($name->get('context'), $name->get('value'));
                $translation->addReference($fileLocation);
                $event->addTranslation($translation);
                $names[] = $name->get('value');
            }
        }
    }

    private function getTraitProperties(ReflectionClass $classRefl)
    {
        $traitProps = [];

        foreach ($classRefl->getTraits() as $traitRefl)
        {
            $traitProps += $traitRefl->getDefaultProperties();
            $traitProps += $this->getTraitProperties($traitRefl);
        }

        return $traitProps;
    }
}
