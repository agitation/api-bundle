<?php

namespace Agit\ApiBundle\Plugin\Seed;

use Agit\PluggableBundle\Strategy\Seed\SeedPluginInterface;
use Agit\PluggableBundle\Strategy\Seed\SeedPlugin;
use Agit\PluggableBundle\Strategy\Seed\SeedEntry;
use Agit\IntlBundle\Translate;

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
            "agit.api" => Translate::noopX("API", "logging category"),
            "agit.api.entity" => Translate::noopX("Entity API", "logging category")
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
