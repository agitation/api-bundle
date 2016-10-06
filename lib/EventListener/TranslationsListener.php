<?php

/*
 * @package    agitation/api-bundle
 * @link       http://github.com/agitation/api-bundle
 * @author     Alexander Günsche
 * @license    http://opensource.org/licenses/MIT
 */

namespace Agit\ApiBundle\EventListener;

use ReflectionClass;
use Agit\ApiBundle\Service\ObjectMetaService;
use Agit\IntlBundle\Event\BundleTranslationsEvent;
use Gettext\Translation;
use Symfony\Component\HttpKernel\KernelInterface;

class TranslationsListener
{
    private $objectMeta;

    public function __construct(ObjectMetaService $objectMeta, KernelInterface $kernel)
    {
        $this->objectMeta = $objectMeta;
        $this->kernel = $kernel;
    }

    public function onRegistration(BundleTranslationsEvent $event)
    {
        $objectNames = $this->objectMeta->getObjectNames();

        $bundleAlias = $event->getBundleAlias();
        $bundle = $this->kernel->getBundle($bundleAlias);
        $bundleNamespace = $bundle->getNamespace();
        $bundlePath = $bundle->getPath();

        foreach ($objectNames as $objectName) {
            $objectClass = $this->objectMeta->getObjectClass($objectName);

            if (strpos($objectClass, $bundleNamespace) !== 0) {
                continue;
            }

            $classRefl = new ReflectionClass($objectClass);
            $fileLocation = "@" . str_replace($bundlePath, $bundleAlias, $classRefl->getFileName());

            $propMetas = $this->objectMeta->getObjectPropertyMetas($objectName);

            foreach ($propMetas as $propMeta) {
                $name = $propMeta->get("Name");
                $translation = new Translation($name->get("context"), $name->get("value"));
                $translation->addReference($fileLocation);
                $event->addTranslation($translation);
            }
        }
    }
}
