<?php

/*
 * @package    agitation/api-bundle
 * @link       http://github.com/agitation/api-bundle
 * @author     Alexander Günsche
 * @license    http://opensource.org/licenses/MIT
 */

namespace Agit\ApiBundle\EventListener;

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
        $list = [];
        $bundleNamespace = $this->kernel->getBundle($event->getBundleAlias())->getNamespace();

        foreach ($objectNames as $objectName) {
            $objectClass = $this->objectMeta->getObjectClass($objectName);

            if (strpos($objectClass, $bundleNamespace) !== 0) {
                continue;
            }

            $propMetas = $this->objectMeta->getObjectPropertyMetas($objectName);

            foreach ($propMetas as $propMeta) {
                $name = $propMeta->get("Name");
                $event->addTranslation(new Translation($name->get("context"), $name->get("value")));
            }
        }
    }
}
