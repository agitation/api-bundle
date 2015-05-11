<?php
/**
 * @package    agitation/api
 * @link       http://github.com/agitation/AgitApiBundle
 * @author     Alex Günsche <http://www.agitsol.com/>
 * @copyright  2012-2015 AGITsol GmbH
 * @license    http://opensource.org/licenses/MIT
 */

namespace Agit\ApiBundle\Api\Formatter;

use Agit\ApiBundle\Exception\IncompatibleFormatterException;

class JsonpFormatter extends JsonFormatter
{
    static protected $mimeType = 'application/javascript';

    static protected $format = 'jsonp';

    protected function getHttpHeaders()
    {
        // extra check to prevent SOP circumvention
        if (!$this->Endpoint->getMeta('Security')->get('allowCrossOrigin'))
            throw new IncompatibleFormatterException("This endpoint does not allow cross-origin requests.");

        return parent::getHttpHeaders();
    }

    protected function getHttpContent()
    {
        $httpContent = parent::getHttpContent();
        $callbackName = $this->Request->get('callback') ?: 'jsonpCallback';

        if (preg_match('|[^a-z0-9_-]|i', $callbackName))
            throw new IncompatibleFormatterException("The callback function name is invalid.");

        return "$callbackName($httpContent);";
    }
}
