<?php

namespace Agit\ApiBundle\Service;

use Agit\IntlBundle\Tool\Translate;
use Agit\SeedBundle\Event\SeedEvent;

class LogentryCategorySeed
{
    public function registerSeed(SeedEvent $event)
    {
        $categories = [
            "agit.api" => Translate::noopX("logging category", "API"),
            "agit.api.entity" => Translate::noopX("logging category", "Entity API")
        ];

        foreach ($categories as $id => $name)
            $event->addSeedEntry("AgitLoggingBundle:LogentryCategory", [ "id" => $id, "name" => $name ]);
    }
}
