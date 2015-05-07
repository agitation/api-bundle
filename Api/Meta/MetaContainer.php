<?php
/**
 * @package    agitation/api
 * @link       http://github.com/agitation/AgitApiBundle
 * @author     Alex Günsche <http://www.agitsol.com/>
 * @copyright  2012-2015 AGITsol GmbH
 * @license    http://opensource.org/licenses/MIT
 */

namespace Agit\ApiBundle\Api\Meta;

use Agit\CoreBundle\Exception\InternalErrorException;

class MetaContainer
{
    private $MetaList = [];

    public function set($name, AbstractMeta $Meta)
    {
        $this->MetaList[$name] = $Meta;
    }

    public function get($name)
    {
        if (!isset($this->MetaList[$name]))
            throw new InternalErrorException("No meta named '$name' found.");

        return $this->MetaList[$name];
    }
}