<?php

/*
 * @package    agitation/api-bundle
 * @link       http://github.com/agitation/api-bundle
 * @author     Alexander Günsche
 * @license    http://opensource.org/licenses/MIT
 */

namespace Agit\ApiBundle\Command;

use Exception;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CollectControllersCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName("agit:api:collect")
            ->setDescription("Collects API stuff");
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            $this->getContainer()->get("agit.api.controller_processor")->process();
            $this->getContainer()->get("agit.api.object_processor")->process();
            $formatter = $this->getContainer()->get("agit.api.formatter")->getFormatter("json");
        } catch (Exception $e) {
            p($e->getMessage());
            p($e->getTraceAsString());
        }
    }
}
