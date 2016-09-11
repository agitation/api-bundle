<?php

/*
 * @package    agitation/api-bundle
 * @link       http://github.com/agitation/api-bundle
 * @author     Alexander Günsche
 * @license    http://opensource.org/licenses/MIT
 */

namespace Agit\ApiBundle\Pluggable;

use Agit\BaseBundle\Pluggable\ProcessorFactoryInterface;
use Doctrine\Common\Annotations\Reader;
use Doctrine\Common\Cache\CacheProvider;
use Doctrine\ORM\EntityManager;

abstract class AbstractApiProcessorFactory implements ProcessorFactoryInterface
{
    protected $cacheProvider;

    protected $annotationReader;

    protected $entityManager;

    public function __construct(Reader $annotationReader, CacheProvider $cacheProvider, EntityManager $entityManager = null)
    {
        $this->annotationReader = $annotationReader;
        $this->cacheProvider = $cacheProvider;
        $this->entityManager = $entityManager;
    }
}
