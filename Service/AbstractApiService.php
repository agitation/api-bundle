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
        {
            $className = $meta['class'];
            $Meta = new $className($meta['options']);
            $MetaContainer->set($name, $Meta);
        }

        return $MetaContainer;
    }
}
