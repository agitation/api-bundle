<?php

/*
 * @package    agitation/api-bundle
 * @link       http://github.com/agitation/api-bundle
 * @author     Alexander Günsche
 * @license    http://opensource.org/licenses/MIT
 */

namespace Agit\ApiBundle\Command;

use Agit\BaseBundle\Command\SingletonCommandTrait;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;

class ApiJsGeneratorCommand extends ContainerAwareCommand
{
    use SingletonCommandTrait;

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
        if (! $this->flock(__FILE__)) {
            return;
        }

        $this->output = $output;
        $this->filesystem = new Filesystem();

        $bundle = $this->getContainer()->get("kernel")->getBundle($input->getArgument("bundle"));
        $bundleNamespace = $bundle->getNamespace();

        $bundlePath = $bundle->getPath();
        $targetPath = "$bundlePath/{$this->relJsPath}/var";

        $endpoints = $this->generateEndpointsFiles($bundleNamespace);
        $objects = $this->generateObjectsFiles($bundleNamespace);

        if (! $endpoints && ! $objects) {
            return;
        }

        if (! is_dir($targetPath)) {
            $this->filesystem->mkdir($targetPath);
        }

        $this->createJsFiles($targetPath, $endpoints, $objects);

        $this->output->writeln("Finished successfully.");
    }

    private function generateEndpointsFiles($bundleNamespace)
    {
        $endpointService = $this->getContainer()->get("agit.api.endpoint");
        $names = $endpointService->getEndpointNames();
        $list = [];

        $this->output->write("Processing endpoints ");

        foreach ($names as $name) {
            $metaContainer = $endpointService->getEndpointMetaContainer($name);

            if (strpos($endpointService->getControllerClass($name), $bundleNamespace) !== 0) {
                continue;
            }

            $list[$name] = [
                $metaContainer->get("Endpoint")->get("request"),
                $metaContainer->get("Endpoint")->get("response")
            ];

            $this->output->write(".");
        }

        $this->output->writeln(sprintf(" %s found.", count($list)));

        return $list;
    }

    private function generateObjectsFiles($bundleNamespace)
    {
        $objectService = $this->getContainer()->get("agit.api.objectmeta");
        $objectNames = $objectService->getObjectNames();
        $list = [];

        $this->output->write("Processing objects ");

        foreach ($objectNames as $objectName) {
            $object = $objectService->createObject($objectName, true);

            if (strpos(get_class($object), $bundleNamespace) !== 0) {
                continue;
            }

            $objMeta = $objectService->getObjectMetas($objectName)->get("Object");
            $meta = [];

            if ($objMeta->get("parentObjectName")) {
                $meta["parent"] = $objMeta->get("parentObjectName");
            }

            $propsMetas = $objectService->getObjectPropertyMetas($objectName);
            $defaults = $objectService->getDefaultValues($objectName);
            $properties = [];

            foreach ($propsMetas as $key => $propMeta) {
                $properties[$key] = $this->extractTypeMeta($propMeta->get("Type"));

                if ($defaults[$key] !== null) {
                    $properties[$key]["default"] = $defaults[$key];
                }
            }

            $list[$objectName] = ["props" => $properties];

            if (count($meta)) {
                $list[$objectName]["meta"] = $meta;
            }
        }

        $this->output->writeln(sprintf(" %s found.", count($list)));

        return $list;
    }

    private function extractTypeMeta($typeMeta)
    {
        $meta = ["type" => $typeMeta->getType()];

        $keywords = ["minLength", "nullable", "readonly", "maxLength", "minValue",
            "maxValue", "positive", "allowFloat", "allowLineBreaks", "class"];

        foreach ($typeMeta->getOptions() as $key => $value) {
            if (in_array($key, $keywords) && $value !== null && $value !== false) {
                $meta[$key] = $value;
            } elseif ($key === "allowedValues" && $value !== null) {
                $meta["values"] = $value;
            }
        }

        return $meta;
    }

    private function createJsFiles($path, $endpoints, $objects)
    {
        $file = "";

        if ($endpoints) {
            $endpointsJson = json_encode($endpoints, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
            $file .= "ag.api.Endpoint.register($endpointsJson);\n";
        }

        if ($objects) {
            $objectsJson = json_encode($objects, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
            $file .= "ag.api.Object.register($objectsJson);\n";
        }

        file_put_contents("$path/api.js", $file);
    }
}
