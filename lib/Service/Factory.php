<?php

/*
 * @package    agitation/api-bundle
 * @link       http://github.com/agitation/api-bundle
 * @author     Alexander Günsche
 * @license    http://opensource.org/licenses/MIT
 */

namespace Agit\ApiBundle\Service;

use Agit\ApiBundle\Annotation\Depends;
use Agit\BaseBundle\Exception\InternalErrorException;
use ReflectionClass;
use Symfony\Component\DependencyInjection\ContainerInterface;

class Factory
{
    private $reflMap = [];

    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function create($class, Depends $depends)
    {
        $deps = [];

        foreach ($depends->get("value") as $dep) {
            if ($dep[0] === "@") {
                $dep = substr($dep, 1);
                $deps[] = $this->container->get($dep);
            } elseif ($dep[0] === "%") {
                $dep = substr($dep, 1, -1);
                $deps[] = $this->container->getParameter($dep);
            } else {
                throw new InternalErrorException("Invalid dependency: $dep.");
            }
        }

        // TODO: PHP >= 5.6
        // if (PHP_VERSION_ID > 50600) {
        //     $instance = new $class(...$deps);
        // } else {
            if (! isset($this->reflMap[$class])) {
                $this->reflMap[$class] = new ReflectionClass($class);
            }

            $instance = $this->reflMap[$class]->newInstanceArgs($deps);
        // }

        return $instance;
    }
}
