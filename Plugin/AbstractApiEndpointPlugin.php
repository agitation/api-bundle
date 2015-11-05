<?php
/**
 * @package    agitation/api
 * @link       http://github.com/agitation/AgitApiBundle
 * @author     Alex Günsche <http://www.agitsol.com/>
 * @copyright  2012-2015 AGITsol GmbH
 * @license    http://opensource.org/licenses/MIT
 */

namespace Agit\ApiBundle\Plugin;

use Agit\CoreBundle\Helper\StringHelper;
use Agit\ApiBundle\Api\Meta\AbstractMeta;

abstract class AbstractApiEndpointPlugin extends AbstractApiPlugin
{
    // API namespace, to be provided by the plugin
    abstract protected function getApiNamespace();

    final protected function getType()
    {
        return 'endpoint';
    }

    final protected function getBaseClass()
    {
        return 'Agit\ApiBundle\Api\Endpoint\AbstractEndpoint';
    }

    final protected function process(\ReflectionClass $classRefl)
    {
        foreach ($classRefl->getMethods() as $methodRefl)
        {
            $annotationList = $this->getService('annotation_reader')->getMethodAnnotations($methodRefl);
            $callMeta = [];

            foreach ($annotationList as $annotation)
            {
                if (!($annotation instanceof AbstractMeta))
                    continue;

                $callMetaName = StringHelper::getBareClassName(get_class($annotation));
                $callMeta[$callMetaName] = $annotation;
            }

            if (!isset($callMeta['Call']) || !isset($callMeta['Security']))
                continue;

            // fix implicit namespaces in request and response
            $callMeta['Call']->set('request', $this->fixObjectName($callMeta['Call']->get('request')));
            $callMeta['Call']->set('response', $this->fixObjectName($callMeta['Call']->get('response')));

            if ($callMeta['Call']->get('listobject'))
                $callMeta['Call']->set('listobject', $this->fixObjectName($callMeta['Call']->get('listobject')));

            $callMeta['Call']->setReference($this->getApiNamespace(), $classRefl->getShortName(), $methodRefl->getName());

            $endpointCall = sprintf(
                "%s/%s.%s",
                $this->getApiNamespace(),
                $classRefl->getShortName(),
                $methodRefl->getName());

            $this->registerEntry($endpointCall, [
                'class' => $classRefl->getName(),
                'meta' => $this->dissectMetaList($callMeta)
            ]);
        }
    }
}
