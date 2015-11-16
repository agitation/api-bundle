<?php
/**
 * @package    agitation/api
 * @link       http://github.com/agitation/AgitApiBundle
 * @author     Alex Günsche <http://www.agitsol.com/>
 * @copyright  2012-2015 AGITsol GmbH
 * @license    http://opensource.org/licenses/MIT
 */

namespace Agit\ApiBundle\Plugin\ApiFormatter;

use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Agit\ApiBundle\Annotation\Formatter\Formatter;

/**
 * @Formatter(mimeType="application/json", format="json")
 */
class JsonFormatter extends AbstractSerializableFormatter
{
    protected function getEncoder()
    {
        return new JsonEncoder();
    }
}
