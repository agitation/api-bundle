<?php
/**
 * @package    agitation/api
 * @link       http://github.com/agitation/AgitApiBundle
 * @author     Alex Günsche <http://www.agitsol.com/>
 * @copyright  2012-2015 AGITsol GmbH
 * @license    http://opensource.org/licenses/MIT
 */

namespace Agit\ApiBundle\EventListener;

use Agit\CoreBundle\Service\ClassCollector;
use Doctrine\Common\Annotations\Reader;

/**
 * Creates object collector listeners.
 */
class ApiPluginListenerFactory
{
    protected $annotationReader;

    protected $classCollector;

    protected $parentClass;

    public function __construct(Reader $annotationReader, ClassCollector $classCollector, $type, $parentClass)
    {
        $this->annotationReader = $annotationReader;
        $this->classCollector = $classCollector;
        $this->type = $type;
        $this->parentClass = $parentClass;
    }

    public function create($namespace, $searchPath, $priority = 100)
    {
        return new ApiPluginListener($this->annotationReader, $this->classCollector, $this->type, $this->parentClass, $namespace, $searchPath, $priority);
    }
}