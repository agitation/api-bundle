<?php
declare(strict_types=1);

/*
 * @package    agitation/api-bundle
 * @link       http://github.com/agitation/api-bundle
 * @author     Alexander Günsche
 * @license    http://opensource.org/licenses/MIT
 */

namespace Agit\ApiBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CreateApiCacheCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('agit:api:cache')
            ->setDescription('Creates the cache of API endpoints and commands.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->getContainer()->get('agit.api.controller_processor')->process();
        $this->getContainer()->get('agit.api.object_processor')->process();
    }
}
