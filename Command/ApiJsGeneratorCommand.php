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
use Agit\CoreBundle\Command\AbstractCommand;
use Symfony\Component\Filesystem\Filesystem;

class ApiJsGeneratorCommand extends AbstractCommand
{
    private $relJsPath = 'Resources/public/js/api';

    private $output;

    private $Filesystem;

    protected function configure()
    {
        $this
            ->setName('agit:api:generate:js')
            ->setDescription('Generate JS lists of a bundle’s endpoints and objects.')
            ->addArgument('bundle', InputArgument::REQUIRED, 'bundle for which the JS should be generated (e.g. FooBarBundle).');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if (!$this->flock(__FILE__)) return;

        $this->output = $output;
        $this->Filesystem = new Filesystem();

        $targetPath = $this->getContainer()->get('agit.core.filecollector')->resolve($input->getArgument('bundle'));

        if (!$targetPath)
            throw new \Exception(sprintf("Invalid bundle: %s", $input->getArgument('bundle')));

        $targetPath .= $this->relJsPath;

        if (!is_dir($targetPath))
            $this->Filesystem->mkdir($targetPath);

        $endpointJsList = $this->generateEndpointsFiles();
        $objectJsList = $this->generateObjectsFiles();

        $this->createJsFiles($targetPath, 'endpoint', 'ApiEndpoints', $endpointJsList);
        $this->createJsFiles($targetPath, 'object', 'ApiObjects', $objectJsList);

        $this->output->writeln('Finished successfully.');
    }

    private function generateEndpointsFiles()
    {
        $AnnotationReader = $this->getContainer()->get('annotation_reader');
        $EndpointService = $this->getContainer()->get('agit.api.endpoint');
        $endpointList = $EndpointService->getEndpointNames();

        $jsLists = [];
        $count = 0;

        $this->output->write("Processing endpoints ");

        foreach ($endpointList as $endpointName => $details)
        {
            $namespace = strstr($endpointName, '/', true);
            $Endpoint = $EndpointService->createEndpoint($endpointName, null);
            $requestObjectName = $Endpoint->getMeta('Call')->get('request');

            if ($requestObjectName)
            {
                if (!isset($jsLists[$namespace]))
                    $jsLists[$namespace] = [];

                $jsLists[$namespace][$endpointName] = $requestObjectName;
                $this->output->write('.');
                ++$count;
            }
        }

        $this->output->writeln(" $count endpoints processed.");

        return $jsLists;
    }

    private function generateObjectsFiles()
    {
        $objectList = $this->getContainer()->get('agit.api.object')->getObjectNames();
        $jsLists = [];
        $count = 0;

        $this->output->write("Processing objects ");

        foreach ($objectList as $objectName => $details)
        {
            $namespace = strstr($objectName, '/', true);
            $ReflObj = new \ReflectionClass($details['class']);
            $defaultValues = $ReflObj->getDefaultProperties();
            $values = [];

            if (!isset($jsLists[$namespace]))
                $jsLists[$namespace] = [];

            foreach ($details['propMetaList'] as $propName => $meta)
            {
                $values[$propName] = [
                    'name' => $meta['Name']['options']['value'],
                    'default' => (isset($defaultValues[$propName])) ? $defaultValues[$propName] : null
                ];

                if ($form = $this->getFormConfig($meta))
                    $values[$propName]['form'] = $form;

                $this->output->write('.');
            }

            $jsLists[$namespace][$objectName] = $values;
            ++$count;
        }

        $this->output->writeln(" $count object processed.");

        return $jsLists;
    }

    private function getFormConfig($meta)
    {
        $form = null;

        if (isset($meta['Form']))
        {
            $form = [];
            $form['type'] = $meta['Form']['options']['type'];

            if (is_array($meta['Form']['options']['values']))
                $form['values'] = $meta['Form']['options']['values'];

            foreach ($meta['Type']['options'] as $key => $value)
            {
                if (in_array($key, ['minLength', 'maxLength', 'minValue', 'maxValue', 'positive', 'allowFloat', 'class']) && $value !== null)
                {
                    $form[$key] = $value;
                }
                elseif (in_array($key, ['nullable', 'readonly']) && $value)
                {
                    $form[$key] = $value;
                }
                elseif ($key === 'allowedValues' && !isset($form['values']))
                {
                    $form['values'] = $value;
                }
            }
        }

        return $form;
    }

    private function createJsFiles($path, $type, $propName, $jsLists)
    {
        foreach ($jsLists as $namespace => $elements)
        {
            $jsFile  = "/*jslint white: true */\n/*global Agit */\n\n";
            $jsFile .= "Agit.$propName = Agit.$propName || {};\n\n";
            $jsFile .= sprintf("Agit.%s['%s'] = %s;\n",$propName, $namespace, json_encode($elements, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
            file_put_contents("$path/{$type}s-$namespace.js", $jsFile);
        }
    }
}
