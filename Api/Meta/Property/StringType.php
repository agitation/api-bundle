<?php
/**
 * @package    agitation/api
 * @link       http://github.com/agitation/AgitApiBundle
 * @author     Alex Günsche <http://www.agitsol.com/>
 * @copyright  2012-2015 AGITsol GmbH
 * @license    http://opensource.org/licenses/MIT
 */

namespace Agit\ApiBundle\Api\Meta\Property;

use Agit\ApiBundle\Exception\InvalidObjectValueException;

/**
 * @Annotation
 */
class StringType extends AbstractType
{
    protected $allowLineBreaks = false;

    protected $allowedValues = null;

    protected $minLength = null;

    protected $maxLength = null;

    protected $_isScalarType = true;

    public function check($value)
    {
        $this->init($value);

        if ($this->mustCheck())
        {
            if (is_array($this->allowedValues))
                static::$_ValidationService->validate('selection', $value, $this->allowedValues);

            if ($this->minLength || $value !== '')
            {
                static::$_ValidationService->validate('string', $value, $this->minLength, $this->maxLength, !$this->allowLineBreaks);

                $forbiddenCharacters = [
                    "<" => "<",
                    "\0" => "null byte"
                ];

                foreach ($forbiddenCharacters as $char => $name)
                    if (strpos($value, $char))
                        throw new InvalidObjectValueException(sprintf(static::$_TranslationService->t("The “%s” character must not be contained."), $name));
            }
        }
    }
}
