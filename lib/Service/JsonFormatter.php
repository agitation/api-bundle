<?php

/*
 * @package    agitation/api-bundle
 * @link       http://github.com/agitation/api-bundle
 * @author     Alexander Günsche
 * @license    http://opensource.org/licenses/MIT
 */

namespace Agit\ApiBundle\Service;

class JsonFormatter extends AbstractSerializableFormatter
{
    public function getMimeType()
    {
        return "application/json";
    }

    protected function encode($result)
    {
        $opts = JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE;

        if ($this->debug) {
            $opts += JSON_PRETTY_PRINT;
        }

        return json_encode($result, $opts);
    }
}
