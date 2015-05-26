<?php
/**
 * @package    agitation/api
 * @link       http://github.com/agitation/AgitApiBundle
 * @author     Alex Günsche <http://www.agitsol.com/>
 * @copyright  2012-2015 AGITsol GmbH
 * @license    http://opensource.org/licenses/MIT
 */

namespace Agit\ApiBundle\EventListener;

use Doctrine\Common\Annotations\Reader;
use Agit\CoreBundle\Exception\InternalErrorException;
use Agit\CoreBundle\Service\ClassCollector;
use Agit\CoreBundle\Pluggable\Strategy\Cache\CacheRegistrationEvent;

/**
 * Reusable listener that collects plugin objects from a given path. "Reusable"
 * means that you can use an instance of this listener as service, without the
 * need of creating a derived class or own implementation.
 */
class ApiPluginListener extends AbstractApiPluginListener
{
    private $ClassCollector;

    private $namespace;

    private $searchPath;

    private $type;

    private $parentClass;

    private $priority;

    private $RegistrationEvent;

    public function __construct(Reader $AnnotationReader, ClassCollector $ClassCollector, $type, $parentClass, $namespace, $searchPath, $priority)
    {
        parent::__construct($AnnotationReader);
        $this->ClassCollector = $ClassCollector;
        $this->type = $type;
        $this->parentClass = $parentClass;
        $this->namespace = $namespace;
        $this->searchPath = $searchPath;
        $this->priority = $priority;
    }

    protected function getRegistrationEvent()
    {
        return $this->RegistrationEvent;
    }

    protected function getNamespace()
    {
        return $this->namespace;
    }

    protected function getPriority()
    {
        return $this->priority;
    }

    /**
     * the event listener to be used in the service configuration
     */
    public function onRegistration(CacheRegistrationEvent $RegistrationEvent)
    {
        $this->RegistrationEvent = $RegistrationEvent;

        foreach ($this->ClassCollector->collect($this->searchPath) as $class)
        {
            $ClassRefl = new \ReflectionClass($class);

            if (!$ClassRefl->isSubclassOf($this->parentClass))
                continue;

            if ($this->type === 'endpoint')
                $this->processEndpoint($ClassRefl);
            elseif ($this->type === 'object')
                $this->processObject($ClassRefl);
            elseif ($this->type === 'formatter')
                $this->processFormatter($ClassRefl);
            else
                throw new InternalErrorException("Invalid API plugin type: {$this->type}.");
        }
    }
}
