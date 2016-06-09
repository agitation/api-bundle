<?php
/**
 * @package    agitation/api
 * @link       http://github.com/agitation/AgitApiBundle
 * @author     Alex Günsche <http://www.agitsol.com/>
 * @copyright  2012-2015 AGITsol GmbH
 * @license    http://opensource.org/licenses/MIT
 */

namespace Agit\ApiBundle\Plugin\ApiFormatter;

use Agit\ApiBundle\Annotation\Formatter\Formatter;

/**
 * @Formatter(mimeType="application/json", format="json")
 */
class JsonFormatter extends AbstractSerializableFormatter
{
    protected function encode($result)
    {
        $opts = JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE;

        if ($this->debug)
            $opts += JSON_PRETTY_PRINT;

        return json_encode($result, $opts);
    }
}
