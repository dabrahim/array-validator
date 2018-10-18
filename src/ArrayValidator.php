<?php

namespace Dabrahim\ArrayValidator;


use Dabrahim\ArrayValidator\Exceptions\InvalidConstraintTypeException;
use Dabrahim\ArrayValidator\Exceptions\InvalidTemplateFormatException;
use Dabrahim\ArrayValidator\Exceptions\InvalidValueFormat;
use Dabrahim\ArrayValidator\Exceptions\MissingKeyException;
use Dabrahim\ArrayValidator\Exceptions\StringValidator;
use Dabrahim\ArrayValidator\Exceptions\UnknownTemplateTypeException;

class ArrayValidator {
    private $_subjectArray;
    private $_constraints;
    private $_missingStringTemplate = self::TEMPLATE_MISSING_KEY;
    private $_invalidValueFormatTemplate = self::TEMPLATE_INVALID_VALUE_FORMAT;
    private $_defaultConstraintType = self::TYPE_NAME;
    //private $_mandatoryConstraintObjectProperties = array();
    /**
     * Types
     */
    const TYPE_NAME = StringValidator::TYPE_NAME;
    const TYPE_EMAIL = StringValidator::TYPE_EMAIL;

    /**
     * Templates
     */
    const TEMPLATE_MISSING_KEY = "The key '{:key}' is mandatory";
    const TEMPLATE_INVALID_VALUE_FORMAT = "The format of '{:key} does not meet the requirements.'";

    public function __construct(array $subjectArray, array $constraints) {
        $this->_constraints = $constraints;
        $this->_subjectArray = $subjectArray;
    }

    /**
     * @throws InvalidConstraintTypeException
     * @throws InvalidValueFormat
     * @throws MissingKeyException
     */
    public function validate() {
        $constraints = $this->_constraints;
        $subject = $this->_subjectArray;

        $keys = array_keys($constraints);
        foreach ($keys as $key) {
            $constraint = $constraints[$key];
            $keyDisplayName = property_exists($constraint, 'prettyName') ? $constraint->prettyName : $key;

            if (array_key_exists($key, $subject)) {

                if(!isset($constraint->type)) {
                    $constraint->type = $this->_defaultConstraintType;
                }

                if(!in_array($constraint->type, array(self::TYPE_EMAIL, self::TYPE_NAME))) {
                    throw new InvalidConstraintTypeException("The constraint type of the key '$key' is unknown");
                }

                /**
                 * REQUIREMENT EXCEPTION
                 */
                if(!StringValidator::validate($subject[$key], $constraint->type)) {
                    throw new InvalidValueFormat(str_replace('{:key}', $keyDisplayName, $this->_invalidValueFormatTemplate));
                }

            } else {
                /**
                 * REQUIREMENT EXCEPTION
                 */
                throw new MissingKeyException(str_replace("{:key}", $keyDisplayName, $this->_missingStringTemplate));
            }
        }
    }

    /**
     * @param $templateType
     * @param $template
     * @throws InvalidTemplateFormatException
     * @throws UnknownTemplateTypeException
     */
    public function setTemplate($templateType, $template) {
        self::checkTemplateFormat($template);
        switch ($templateType) {
            case self::TEMPLATE_MISSING_KEY:
                $this->_missingStringTemplate = $template;
                break;
            case self::TEMPLATE_INVALID_VALUE_FORMAT:
                $this->_invalidValueFormatTemplate = $template;
                break;
            default:
                throw new UnknownTemplateTypeException("The given template type is unknown. Use one of the ones specified in the class constants ArrayValidator::TYPE_*");
                break;
        }
    }

    /**
     * @param $template
     * @throws InvalidTemplateFormatException
     */
    private static function checkTemplateFormat ($template) {
        if(strpos($template, '{:key}') === false) {
            throw new InvalidTemplateFormatException("The given template format is invalid. Please check the correct syntax.");
        }
    }

    /**
     * @param $type
     * @throws InvalidConstraintTypeException
     */
    public function setDefaultConstraintType ($type) {
        if(in_array($type, array(self::TYPE_NAME, self::TYPE_EMAIL))) {
            $this->_defaultConstraintType = $type;
        } else {
            throw new InvalidConstraintTypeException("The constraint type '$type' is unknown");
        }
    }
}