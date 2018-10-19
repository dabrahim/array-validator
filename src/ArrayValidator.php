<?php

namespace Dabrahim\ArrayValidator;


use Dabrahim\ArrayValidator\Exceptions\InvalidConstraintTypeException;
use Dabrahim\ArrayValidator\Exceptions\InvalidTemplateFormatException;
use Dabrahim\ArrayValidator\Exceptions\InvalidValueFormat;
use Dabrahim\ArrayValidator\Exceptions\MissingConstraintPropertyException;
use Dabrahim\ArrayValidator\Exceptions\MissingKeyException;
use Dabrahim\ArrayValidator\Exceptions\UnknownTemplateTypeException;

class ArrayValidator {
    private $_subjectArray;
    private $_constraints;
    private $_missingStringTemplate = self::TEMPLATE_MISSING_KEY;
    private $_invalidValueFormatTemplate = self::TEMPLATE_INVALID_VALUE_FORMAT;
    private $_notAllowedValueTemplate= self::TEMPLATE_NOT_ALLOWED_VALUE;
    private $_invalidStringLengthTemplate = self::TEMPLATE_INVALID_STRING_LENGTH;
    private $_defaultConstraintType = self::TYPE_SIMPLE_STRING;
    private $_constants;
    /**
     * Types
     */
    const TYPE_NAME = SubjectValidator::TYPE_NAME;
    const TYPE_EMAIL = SubjectValidator::TYPE_EMAIL;
    const TYPE_NUMERIC = SubjectValidator::TYPE_NUMERIC;
    const TYPE_INTEGER = SubjectValidator::TYPE_INTEGER;
    const TYPE_SIMPLE_STRING = "Simple string type";
    const TYPE_IN_COLLECTION = "In Range type";

    /**
     * Templates
     */
    const TEMPLATE_MISSING_KEY = "The key '{:key}' is mandatory";
    const TEMPLATE_INVALID_VALUE_FORMAT = "The format of '{:key}' does not meet the requirements.";
    const TEMPLATE_NOT_ALLOWED_VALUE = "The value of '{:key}' is not allowed. The allowed values are : {:range}";
    const TEMPLATE_INVALID_STRING_LENGTH = "'{:key}' should be at least formed by {:length} characters.";

    public function __construct(array $subjectArray, array $constraints) {
        $this->_constraints = $constraints;
        $this->_subjectArray = $subjectArray;
        try {
            $ref = new \ReflectionClass(__CLASS__);
            $this->_constants = $ref->getConstants();
        } catch (\ReflectionException $e) {
            die("Could not get class constants");
        }
    }

    /**
     * @throws InvalidConstraintTypeException
     * @throws InvalidTemplateFormatException
     * @throws InvalidValueFormat
     * @throws MissingConstraintPropertyException
     * @throws MissingKeyException
     */
    public function validate () {
        $constraints = $this->_constraints;
        $subject = $this->_subjectArray;

        $keys = array_keys($constraints);
        foreach ($keys as $key) {
            $constraint = $constraints[$key];

            if(!is_object($constraint)) {
                $currentType = gettype($constraint);
                throw new InvalidTemplateFormatException("The '$key' constraint must be of type object. $currentType given.");
            }

            $keyDisplayName = property_exists($constraint, 'prettyName') ? $constraint->prettyName : $key;

            if (array_key_exists($key, $subject)) {

                if(!isset($constraint->type)) {
                    $constraint->type = $this->_defaultConstraintType;
                }

                if(!in_array($constraint->type, $this->getConstraintTypesConstants())) {
                    throw new InvalidConstraintTypeException("The constraint type of the key '$key' is unknown");
                }

                /**
                 * REQUIREMENT EXCEPTION
                 */
                if($constraint->type === self::TYPE_IN_COLLECTION) {
                    if(!property_exists($constraint, 'collection')) {
                        throw new MissingConstraintPropertyException("The property 'collection' of $key constraint is mandatory for a collection type constraint.");
                    }

                    if(!in_array($subject[$key], $constraint->collection)) {
                        $message = str_replace('{:key}', $keyDisplayName, $this->_notAllowedValueTemplate);
                        $message = str_replace('{:range}', join('/', $constraint->collection), $message);
                        throw new InvalidValueFormat($message);
                    }

                } else if($constraint->type === self::TYPE_SIMPLE_STRING) {
                    if(property_exists($constraint, 'minLength')) {
                        if( strlen(trim($subject[$key])) < $constraint->minLength ) {
                            $msg = str_replace('{:key}', $keyDisplayName, $this->_invalidStringLengthTemplate);
                            $msg = str_replace('{:length}', $constraint->minLength, $msg);
                            throw new InvalidValueFormat($msg);
                        }
                    }

                } else if(!SubjectValidator::validate($subject[$key], $constraint->type)) {
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
     * @param $template string
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
            case self::TEMPLATE_NOT_ALLOWED_VALUE:
                $this->_notAllowedValueTemplate = $template;
                break;
            case self::TEMPLATE_INVALID_STRING_LENGTH:
                $this->_invalidStringLengthTemplate = $template;
                break;
            default:
                throw new UnknownTemplateTypeException("The given template type is unknown. Use one of the ones specified in the class constants ArrayValidator::TEMPLATE_*");
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
        if(in_array($type, $this->getConstraintTypesConstants())) {
            $this->_defaultConstraintType = $type;
        } else {
            throw new InvalidConstraintTypeException("The constraint type '$type' is unknown");
        }
    }

    /**
     * @return array containing the different types constants
     */
    private function getConstraintTypesConstants () {
        $constants = $this->_constants;
        foreach ($constants as $key => $val) {
            if(!preg_match('/^TYPE_/', $key)) {
                unset($constants[$key]);
            }
        }
        return array_values($constants);
    }
}