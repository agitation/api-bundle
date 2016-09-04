<?php

namespace Agit\ApiBundle\Plugin\Seed;

use Agit\PluggableBundle\Strategy\Seed\SeedPluginInterface;
use Agit\PluggableBundle\Strategy\Seed\SeedPlugin;
use Agit\PluggableBundle\Strategy\Seed\SeedEntry;
use Agit\BaseBundle\Tool\Translate;

/**
 * @SeedPlugin(entity="AgitLoggingBundle:LogentryCategory")
 */
class LogentryCategorySeedPlugin implements SeedPluginInterface
{
    private $seedData = [];

    public function load()
    {
        $this->seedData = [];

        $categories = [
            "agit.api" => Translate::noopX("logging category", "API"),
            "agit.api.entity" => Translate::noopX("logging category", "Entity API")
        ];

        foreach ($categories as $id => $name)
        {
            $seedEntry = new SeedEntry();
            $seedEntry->setDoUpdate(true);
            $seedEntry->setData([ "id" => $id, "name" => $name ]);
            $this->seedData[] = $seedEntry;
        }
    }

    public function nextSeedEntry()
    {
        return array_pop($this->seedData);
    }
}
