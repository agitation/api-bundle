<?php
/**
 * @package    agitation/api
 * @link       http://github.com/agitation/AgitApiBundle
 * @author     Alex Günsche <http://www.agitsol.com/>
 * @copyright  2012-2015 AGITsol GmbH
 * @license    http://opensource.org/licenses/MIT
 */

namespace Agit\ApiBundle\Plugin\Api\Formatter;

use Symfony\Component\Serializer\Encoder\JsonEncoder;

class JsonFormatter extends AbstractSerializableFormatter
{
    static protected $mimeType = 'application/json';

    static protected $format = 'json';

    protected function getEncoder()
    {
        return new JsonEncoder();
    }
}
