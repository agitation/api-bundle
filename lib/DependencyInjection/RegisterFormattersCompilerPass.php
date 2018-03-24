<?php
declare(strict_types=1);

/*
 * @package    agitation/api-bundle
 * @link       http://github.com/agitation/api-bundle
 * @author     Alexander Günsche
 * @license    http://opensource.org/licenses/MIT
 */

namespace Agit\ApiBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class RegisterFormattersCompilerPass implements CompilerPassInterface
{
    private $containerBuilder;

    public function process(ContainerBuilder $containerBuilder)
    {
        $processor = $containerBuilder->findDefinition('agit.api.formatter');
        $services = $containerBuilder->findTaggedServiceIds('agit.api.formatter');

        foreach ($services as $name => $tags)
        {
            foreach ($tags as $tag)
            {
                $processor->addMethodCall('addFormatter', [$tag['extension'], new Reference($name)]);
            }
        }
    }
}
