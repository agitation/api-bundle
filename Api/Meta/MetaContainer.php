<?php
/**
 * @package    agitation/api
 * @link       http://github.com/agitation/AgitApiBundle
 * @author     Alex Günsche <http://www.agitsol.com/>
 * @copyright  2012-2015 AGITsol GmbH
 * @license    http://opensource.org/licenses/MIT
 */

namespace Agit\ApiBundle\Api\Meta;

use Agit\CommonBundle\Exception\InternalErrorException;

class MetaContainer
{
    private $metaList = [];

    public function has($name)
    {
        return isset($this->metaList[$name]);
    }

    public function set($name, AbstractMeta $meta)
    {
        $this->metaList[$name] = $meta;
    }

    public function get($name)
    {
        if (!isset($this->metaList[$name]))
            throw new InternalErrorException("No meta named '$name' found.");

        return $this->metaList[$name];
    }
}
