<?php
declare(strict_types=1);

/*
 * @package    agitation/api-bundle
 * @link       http://github.com/agitation/api-bundle
 * @author     Alexander Günsche
 * @license    http://opensource.org/licenses/MIT
 */

namespace Agit\ApiBundle\Service;

use Agit\IntlBundle\Tool\Translate;
use Agit\SeedBundle\Event\SeedEvent;

class LogentryCategorySeed
{
    public function registerSeed(SeedEvent $event)
    {
        $categories = [
            'agit.api' => Translate::noopX('logging category', 'API'),
            'agit.api.entity' => Translate::noopX('logging category', 'Entity API')
        ];

        foreach ($categories as $id => $name)
        {
            $event->addSeedEntry('AgitLoggingBundle:LogentryCategory', ['id' => $id, 'name' => $name]);
        }
    }
}
