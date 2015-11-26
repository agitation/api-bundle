<?php
/**
 * @package    agitation/api
 * @link       http://github.com/agitation/AgitApiBundle
 * @author     Alex Günsche <http://www.agitsol.com/>
 * @copyright  2012-2015 AGITsol GmbH
 * @license    http://opensource.org/licenses/MIT
 */

namespace Agit\ApiBundle\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Agit\CommonBundle\Command\AbstractCommand;
use Symfony\Component\Filesystem\Filesystem;

class ApiJsGeneratorCommand extends AbstractCommand
{
    private $relJsPath = "Resources/public/js";

    private $output;

    private $filesystem;

    protected function configure()
    {
        $this
            ->setName("agit:api:generate:js")
            ->setDescription("Generate JS lists of a bundle’s endpoints and objects.")
            ->addArgument("bundle", InputArgument::REQUIRED, "bundle for which the JS should be generated (e.g. FooBarBundle).");
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if (!$this->flock(__FILE__)) return;

        $this->output = $output;
        $this->filesystem = new Filesystem();

        $bundle = $this->getContainer()->get("kernel")->getBundle($input->getArgument("bundle"));
        $bundleNamespace = $bundle->getNamespace();

        $bundlePath = $bundle->getPath();
        $targetPath = "$bundlePath/{$this->relJsPath}";

        $endpoints = $this->generateEndpointsFiles($bundleNamespace);
        $objects = $this->generateObjectsFiles($bundleNamespace);

        if (!$endpoints && !$objects)
            return;

        if (!is_dir($targetPath))
            $this->filesystem->mkdir($targetPath);

        $this->createJsFiles($targetPath, $endpoints, $objects);

        $this->output->writeln("Finished successfully.");
    }

    private function generateEndpointsFiles($bundleNamespace)
    {
        $endpointService = $this->getContainer()->get("agit.api.endpoint");
        $endpointNames = $endpointService->getEndpointNames();
        $list = [];

        $this->output->write("Processing endpoints ");

        foreach ($endpointNames as $endpointName)
        {
            $endpoint = $endpointService->createEndpoint($endpointName);

            if (strpos(get_class($endpoint), $bundleNamespace) !== 0) continue;

            $list[$endpointName] = $endpoint->getMeta("Endpoint")->get("request");
            $this->output->write(".");
        }

        $this->output->writeln(sprintf(" %s found.", count($list)));

        return $list;
    }

    private function generateObjectsFiles($bundleNamespace)
    {
        $objectService = $this->getContainer()->get("agit.api.object");
        $objectNames = $objectService->getObjectNames();
        $list = [];

        $this->output->write("Processing objects ");

        foreach ($objectNames as $objectName)
        {
            $object = $objectService->createObject($objectName);

            if (strpos(get_class($object), $bundleNamespace) !== 0) continue;

            $objData = $object->getValues();
            $objProps = [];

            $objRefl = new \ReflectionClass(get_class($object));

            foreach ($objData as $key => $value)
            {
                $propMetas = $object->getPropertyMetas($key);

                $objProps[$key] = $this->getPropMeta($propMetas);
                $objProps[$key]["name"] = $propMetas->get("Name")->get("value");
                $objProps[$key]["default"] = $value;

                $list[$objectName] = $objProps;
            }
        }

        $this->output->writeln(sprintf(" %s found.", count($list)));

        return $list;
    }

    private function getPropMeta($propMetas)
    {
        $typeMeta = $propMetas->get("Type");

        $meta = ["type" => $typeMeta->getType()];

        foreach ($typeMeta->getOptions() as $key => $value)
        {
            if (in_array($key, ["minLength", "maxLength", "minValue", "maxValue", "positive", "allowFloat", "class"]) && $value !== null)
            {
                $meta[$key] = $value;
            }
            elseif (in_array($key, ["nullable", "readonly"]) && $value)
            {
                $meta[$key] = $value;
            }
            elseif ($key === "allowedValues" && $value !== null)
            {
                $meta["values"] = $value;
            }
        }

        return $meta;
    }

    private function createJsFiles($path, $endpoints, $objects)
    {
        $endpointsJson = json_encode($endpoints, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        $objectsJson = json_encode($objects, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

        $file = "/*jslint white: true */\n/*global Agit */\n\n" .
            "Agit.Endpoint.registerList($endpointsJson);\n" .
            "Agit.Object.registerList($objectsJson);";

        file_put_contents("$path/api.js", $file);
    }
}
