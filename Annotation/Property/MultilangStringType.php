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
class MultilangStringType extends StringType
{
    public function check($value)
    {
        $this->init($value);

        if ($this->mustCheck())
        {
            static::$_ValidationService->validate("multilang", $value, $this->minLength, $this->maxLength, $this->allowLineBreaks);
            $this->checkForbiddenCharacters($value);
        }
    }
}
