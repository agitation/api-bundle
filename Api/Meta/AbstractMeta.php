<?php
/**
 * @package    agitation/api
 * @link       http://github.com/agitation/AgitApiBundle
 * @author     Alex Günsche <http://www.agitsol.com/>
 * @copyright  2012-2015 AGITsol GmbH
 * @license    http://opensource.org/licenses/MIT
 */

namespace Agit\ApiBundle\Api\Meta;

use Agit\CoreBundle\Annotation\AbstractAnnotation;
use Agit\CoreBundle\Exception\InternalErrorException;

abstract class AbstractMeta extends AbstractAnnotation
{
    public function setValue($key, $value)
    {
        if (!property_exists($this, $key))
            throw new InternalErrorException(sprintf('Property "%s" does not exist.', $key));

        if ($key[0] === '_')
            throw new InternalErrorException('Internal properties must not be modified from outside.');

        $this->$key = $value;
    }
}