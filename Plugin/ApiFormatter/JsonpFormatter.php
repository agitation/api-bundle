<?php
/**
 * @package    agitation/api
 * @link       http://github.com/agitation/AgitApiBundle
 * @author     Alex Günsche <http://www.agitsol.com/>
 * @copyright  2012-2015 AGITsol GmbH
 * @license    http://opensource.org/licenses/MIT
 */

namespace Agit\ApiBundle\Plugin\ApiFormatter;

use Agit\ApiBundle\Exception\IncompatibleFormatterException;
use Agit\ApiBundle\Annotation\Formatter\Formatter;

/**
 * @Formatter(mimeType="application/javascript", format="jsonp")
 */
class JsonpFormatter extends JsonFormatter
{
    protected function getHttpHeaders()
    {
        // extra check to prevent SOP circumvention
        if (!$this->endpoint->getMeta('Security')->get('allowCrossOrigin'))
            throw new IncompatibleFormatterException("This endpoint does not allow cross-origin requests.");

        return parent::getHttpHeaders();
    }

    protected function getHttpContent()
    {
        $httpContent = parent::getHttpContent();
        $callbackName = $this->request->get('callback') ?: 'jsonpCallback';

        if (preg_match('|[^a-z0-9_-]|i', $callbackName))
            throw new IncompatibleFormatterException("The callback function name is invalid.");

        return "$callbackName($httpContent);";
    }
}
