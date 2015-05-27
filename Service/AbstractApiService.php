<?php
/**
 * @package    agitation/api
 * @link       http://github.com/agitation/AgitApiBundle
 * @author     Alex Günsche <http://www.agitsol.com/>
 * @copyright  2012-2015 AGITsol GmbH
 * @license    http://opensource.org/licenses/MIT
 */

namespace Agit\ApiBundle\Service;

use Agit\ApiBundle\Api\Meta\MetaContainer;

abstract class AbstractApiService
{
    protected function createMetaContainer($metaList)
    {
        $MetaContainer = new MetaContainer();

        foreach ($metaList as $name => $meta)
            $MetaContainer->set($name, $this->composeMeta($meta));

        return $MetaContainer;
    }

    // re-composing meta from class name and options.
    protected function composeMeta($rawMeta)
    {
        $className = $rawMeta['class'];
        return new $className($rawMeta['options']);
    }
}
