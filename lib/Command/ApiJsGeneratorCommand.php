<?php

/*
 * @package    agitation/api-bundle
 * @link       http://github.com/agitation/api-bundle
 * @author     Alexander Günsche
 * @license    http://opensource.org/licenses/MIT
 */

namespace Agit\ApiBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;

class ApiJsGeneratorCommand extends ContainerAwareCommand
{
    private $assetsPath = "Resources/public";

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
        $this->output = $output;
        $this->filesystem = new Filesystem();

        $bundle = $this->getContainer()->get("kernel")->getBundle($input->getArgument("bundle"));
        $bundleNamespace = $bundle->getNamespace();

        $endpoints = $this->generateEndpointsFiles($bundleNamespace);
        $objects = $this->generateObjectsFiles($bundleNamespace);

        if (! $endpoints && ! $objects) {
            return;
        }

        $this->createJsFiles($bundle->getPath(), $endpoints, $objects);

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
            $class = $endpointService->getControllerClass($name);

            if (strpos($class, $bundleNamespace) !== 0) {
                continue;
            }

            $subNs = strstr(str_replace("$bundleNamespace\Api\\", "", $class), "\\", true);
            $path = ($subNs === "Controller" ? "" : strtolower($subNs) . "/") . "js";

            if (! isset($list[$path])) {
                $list[$path] = [];
            }

            $list[$path][$name] = [
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
            $objectClass = $objectService->getObjectClass($objectName);

            if (strpos($objectClass, $bundleNamespace) !== 0) {
                continue;
            }

            $objMeta = $objectService->getObjectMetas($objectName)->get("Object");
            $meta = [];

            $propsMetas = $objectService->getObjectPropertyMetas($objectName);
            $defaults = $objectService->getDefaultValues($objectName);
            $properties = [];

            foreach ($propsMetas as $key => $propMeta) {
                $properties[$key] = $this->extractTypeMeta($propMeta->get("Type"));

                if ($defaults[$key] !== null) {
                    $properties[$key]["default"] = $defaults[$key];
                }
            }

            $subNs = strstr(str_replace("$bundleNamespace\Api\\", "", $objectClass), "\\", true);
            $path = ($subNs === "Object" ? "" : strtolower($subNs) . "/") . "js";

            if (! isset($list[$path])) {
                $list[$path] = [];
            }

            $list[$path][$objectName] = ["props" => $properties];

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

        $keywords = ["minLength", "nullable", "readonly", "maxLength", "minValue", "maxValue", "allowLineBreaks", "class"];

        foreach ($typeMeta->getOptions() as $key => $value) {
            if (in_array($key, $keywords) && $value !== null && $value !== false) {
                $meta[$key] = $value;
            } elseif ($key === "allowedValues" && $value !== null) {
                $meta["values"] = $value;
            }
        }

        return $meta;
    }

    private function createJsFiles($basePath, $endpoints, $objects)
    {
        $files = [];

        foreach (array_unique(array_merge(array_keys($endpoints), array_keys($objects))) as $path) {
            $file = "";

            if (isset($endpoints[$path])) {
                $endpointsJson = json_encode($endpoints[$path], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
                $file .= "ag.api.Endpoint.register($endpointsJson);\n";
            }

            if ($objects[$path]) {
                $objectsJson = json_encode($objects[$path], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
                $file .= "ag.api.Object.register($objectsJson);\n";
            }

            if ($file) {
                $targetPath = "$basePath/{$this->assetsPath}/$path/var";

                if (! is_dir($targetPath)) {
                    $this->filesystem->mkdir($targetPath);
                }

                file_put_contents("$targetPath/api.js", $file);
            }
        }
    }
}
