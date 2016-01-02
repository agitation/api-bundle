<?php
/**
 * @package    agitation/api
 * @link       http://github.com/agitation/AgitApiBundle
 * @author     Alex Günsche <http://www.agitsol.com/>
 * @copyright  2012-2015 AGITsol GmbH
 * @license    http://opensource.org/licenses/MIT
 */

namespace Agit\ApiBundle\Annotation\Property;

/**
 * @Annotation
 */
class EntityListType extends EntityType
{
    protected $minLength = null;

    protected $maxLength = null;

    protected $_isListType = true;

    public function check($value)
    {
        $this->init($value);

        if ($this->mustCheck())
        {
            static::$_ValidationService->validate('array', $value);

            foreach ($value as $val)
                static::$_ValidationService->validate($this->keytype, $val, 1);
        }
    }
}
