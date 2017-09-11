<?php
declare(strict_types=1);
/*
 * @package    agitation/api-bundle
 * @link       http://github.com/agitation/api-bundle
 * @author     Alexander Günsche
 * @license    http://opensource.org/licenses/MIT
 */

namespace Agit\ApiBundle\Command;

use Agit\BaseBundle\Tool\StringHelper;
use Doctrine\ORM\Mapping\ClassMetadataInfo;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class GenerateObjectCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('agit:api:generate:object')
            ->setDescription('Generates an API object from an entity class.')
            ->addArgument('entity', InputArgument::REQUIRED, 'ID of the entity class')
            ->addArgument('bundle', InputArgument::OPTIONAL, 'bundle ID')
            ->addArgument('namespace', InputArgument::OPTIONAL, 'API namespace');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $bundleName = "Foo\BarBundle";

        if ($input->getArgument('bundle'))
        {
            $bundle = $this->getContainer()->get('kernel')->getBundle($input->getArgument('bundle'));
            $bundleName = $bundle->getNamespace();
        }

        $apiNs = $input->getArgument('namespace')
            ? $input->getArgument('namespace')
            : 'stuff.v1';

        $em = $this->getContainer()->get('doctrine.orm.entity_manager');
        $metadata = $em->getClassMetadata($input->getArgument('entity'));
        $className = StringHelper::getBareClassName($metadata->name);
        $assoc = $metadata->getAssociationNames();
        $allFields = array_merge($assoc, $metadata->getFieldNames());

        $tpl = [];

        foreach ($allFields as $prop)
        {
            $attr = '';

            if (! in_array($prop, $assoc))
            {
                $type = $metadata->getTypeOfField($prop);

                if ($type === 'smallint' || $type === 'integer')
                {
                    $attr = "@Property\IntegerType(minValue=, maxValue=)";
                }
                elseif ($type === 'decimal')
                {
                    $attr = "@Property\FloatType(minValue=, maxValue=)";
                }
                elseif ($type === 'text' || $type === 'string')
                {
                    $attr = "@Property\StringType(minLength=, maxLength=)";
                }
                elseif ($type === 'boolean')
                {
                    $attr = "@Property\BooleanType";
                }
            }
            else
            {
                $mapping = $metadata->getAssociationMapping($prop);
                $targetEntity = StringHelper::getBareClassName($mapping['targetEntity']);
                $isOwning = $mapping['isOwningSide'];

                if ($mapping['type'] & ClassMetadataInfo::TO_ONE && ! $mapping['inversedBy'])
                {
                    $attr = "@Property\ObjectType(class=\"$targetEntity\")";
                }
                elseif ($mapping['type'] & ClassMetadataInfo::TO_MANY)
                {
                    $attr = "@Property\ObjectListType(class=\"$targetEntity\", minLength=, maxLength=)";
                }
            }

            if ($attr)
            {
                $tpl[] = "    /**\n" .
                         "     * @Property\Name(\"$prop\")\n" .
                         "     * $attr\n" .
                         "     */\n" .
                         "    public \$$prop;\n";
            }
        }

        $classTpl = sprintf(
            "<?php\n\n" .
            "namespace $bundleName\Api\Object;\n\n" .
            "use Agit\ApiBundle\Annotation\Object;\n" .
            "use Agit\ApiBundle\Annotation\Property;\n" .
            "use Agit\ApiBundle\Api\Object\AbstractEntityObject;\n\n" .
            "/**\n" .
            " * @Object\Object(namespace=\"$apiNs\")\n" .
            " */\n" .
            "class $className extends AbstractEntityObject\n" .
            "{\n%s}\n",
            implode("\n", $tpl)
        );

        $output->write($classTpl);
    }
}
