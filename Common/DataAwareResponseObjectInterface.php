<?php
/**
 * @package    agitation/api
 * @link       http://github.com/agitation/AgitApiBundle
 * @author     Alex Günsche <http://www.agitsol.com/>
 * @copyright  2012-2015 AGITsol GmbH
 * @license    http://opensource.org/licenses/MIT
 */

namespace Agit\ApiBundle\Common;

interface DataAwareResponseObjectInterface
{
    /**
     * Can be implemented by API objects which know what data they expect;
     * especially handy for response objects resembling entities.
     *
     * NOTE: The $data parameter is not part of the signature in order to
     * allow type hinting.
     *
     * @param mixed $data
     * @return void
     */
    public function fill();
}
