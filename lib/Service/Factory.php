<?php
declare(strict_types=1);
/*
 * @package    agitation/api-bundle
 * @link       http://github.com/agitation/api-bundle
 * @author     Alexander Günsche
 * @license    http://opensource.org/licenses/MIT
 */

namespace Agit\ApiBundle\Service;

use Agit\ApiBundle\Annotation\Depends;
use Agit\BaseBundle\Exception\InternalErrorException;
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

        foreach ($depends->get('value') as $dep)
        {
            if ($dep[0] === '@')
            {
                $dep = substr($dep, 1);
                $deps[] = $this->container->get($dep);
            }
            elseif ($dep[0] === '%')
            {
                $dep = substr($dep, 1, -1);
                $deps[] = $this->container->getParameter($dep);
            }
            else
            {
                throw new InternalErrorException("Invalid dependency: $dep.");
            }
        }

        return new $class(...$deps);
    }
}
