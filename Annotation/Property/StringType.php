<?php

/*
 * @package    agitation/api-bundle
 * @link       http://github.com/agitation/api-bundle
 * @author     Alexander Günsche
 * @license    http://opensource.org/licenses/MIT
 */

namespace Agit\ApiBundle\Annotation\Property;

use Agit\ApiBundle\Exception\InvalidObjectValueException;
use Agit\IntlBundle\Tool\Translate;

/**
 * @Annotation
 */
class StringType extends AbstractType
{
    protected $allowLineBreaks = false;

    protected $allowedValues = null;

    protected $minLength = null;

    protected $maxLength = null;

    protected $pattern = null;

    protected $_isScalarType = true;

    public function check($value)
    {
        $this->init($value);

        if ($this->mustCheck()) {
            if (is_array($this->allowedValues)) {
                static::$_ValidationService->validate("selection", $value, array_keys($this->allowedValues));
            } else {
                if ($this->minLength || $value !== "") {
                    static::$_ValidationService->validate("string", $value, $this->minLength, $this->maxLength, $this->allowLineBreaks);
                    $this->checkForbiddenCharacters($value);
                }

                if ($this->pattern) {
                    static::$_ValidationService->validate("regex", $value, $this->pattern);
                    $this->checkForbiddenCharacters($value);
                }
            }
        }
    }

    protected function checkForbiddenCharacters($value)
    {
        $forbiddenCharacters = [
            "<"  => "<",
            "\0" => "null byte"
        ];

        foreach ($forbiddenCharacters as $char => $name) {
            if (strpos($value, $char)) {
                throw new InvalidObjectValueException(sprintf(Translate::t("The “%s” character must not be contained."), $name));
            }
        }
    }
}
