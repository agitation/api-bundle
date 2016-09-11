<?php

/*
 * @package    agitation/api-bundle
 * @link       http://github.com/agitation/api-bundle
 * @author     Alexander Günsche
 * @license    http://opensource.org/licenses/MIT
 */

/**
 * @link       http://github.com/agitation/AgitApiBundle
 *
 * @author     Alex Günsche <http://www.agitsol.com/>
 * @copyright  2012-2015 AGITsol GmbH
 * @license    http://opensource.org/licenses/MIT
 */

namespace Agit\ApiBundle\Service;

use Agit\ApiBundle\Annotation\MetaContainer;

trait MetaAwareTrait
{
    protected function createMetaContainer(array $metaList)
    {
        $metaContainer = new MetaContainer();

        foreach ($metaList as $name => $meta) {
            $metaContainer->set($name, $this->composeMeta($meta));
        }

        return $metaContainer;
    }

    // re-composing meta from class name and options.
    protected function composeMeta($rawMeta)
    {
        $className = $rawMeta['class'];

        return new $className($rawMeta['options']);
    }
}
