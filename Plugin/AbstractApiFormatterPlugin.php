<?php
/**
 * @package    agitation/api
 * @link       http://github.com/agitation/AgitApiBundle
 * @author     Alex Günsche <http://www.agitsol.com/>
 * @copyright  2012-2015 AGITsol GmbH
 * @license    http://opensource.org/licenses/MIT
 */

namespace Agit\ApiBundle\Plugin;

abstract class AbstractApiFormatterPlugin extends AbstractApiPlugin
{
    final protected function getType()
    {
        return 'formatter';
    }

    final protected function getBaseClass()
    {
        return 'Agit\ApiBundle\Api\Formatter\AbstractFormatter';
    }

    final protected function process(\ReflectionClass $classRefl)
    {
        $properties = $classRefl->getStaticProperties();
        $this->registerEntry($properties['format'], $classRefl->getName());
    }
}
