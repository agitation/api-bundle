<?php
/**
 * @package    agitation/api
 * @link       http://github.com/agitation/AgitApiBundle
 * @author     Alex Günsche <http://www.agitsol.com/>
 * @copyright  2012-2015 AGITsol GmbH
 * @license    http://opensource.org/licenses/MIT
 */

namespace Agit\ApiBundle\Plugin;

use Agit\PluggableBundle\Strategy\Cache\CachePlugin;

/**
 * @CachePlugin(tag="agit.api.formatter")
 */
class ApiFormatterPlugin extends AbstractApiFormatterPlugin
{
    protected function getSearchNamespace()
    {
        return "Agit\ApiBundle\Api\Formatter";
    }
}
