<?php
declare(strict_types=1);
/*
 * @package    agitation/api-bundle
 * @link       http://github.com/agitation/api-bundle
 * @author     Alexander Günsche
 * @license    http://opensource.org/licenses/MIT
 */

namespace Agit\ApiBundle\Service;

use Agit\ApiBundle\Exception\IncompatibleFormatterException;
use Symfony\Component\HttpFoundation\Request;

class JsonpFormatter extends JsonFormatter
{
    public function getMimeType()
    {
        return 'application/javascript';
    }

    protected function getHttpContent(Request $httpRequest, $result)
    {
        $httpContent = parent::getHttpContent($httpRequest, $result);
        $callbackName = $httpRequest->get('callback') ?: 'jsonpCallback';

        if (preg_match('|[^a-z0-9_-]|i', $callbackName))
        {
            throw new IncompatibleFormatterException('The callback function name is invalid.');
        }

        return "$callbackName($httpContent);";
    }
}
