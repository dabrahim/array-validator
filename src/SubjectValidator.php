<?php
namespace Dabrahim\ArrayValidator;


class SubjectValidator {
    const TYPE_NAME = "Name TYPE";
    const TYPE_EMAIL = "Email TYPE";
    const TYPE_NUMERIC = "Numeric Type";
    const TYPE_INTEGER = "Integer type";

    const PATTERN_NAME = '/^[a-zA-z]{2,}( [a-zA-Z]+)?$/';
    const PATTERN_INTEGER = '/^[0-9]+$/';

    /**
     * @param $subject mixed
     * @param $format
     * @return bool
     */
    public static function validate ($subject, $format) {
        switch ($format) {
            case self::TYPE_EMAIL:
                return filter_var($subject, FILTER_VALIDATE_EMAIL) != false;
                break;
            case self::TYPE_NUMERIC:
                return is_numeric($subject);
                break;
            default:
                return preg_match(self::getPattern($format), $subject) === 1;
                break;
        }
    }

    /**
     * @param $format
     * @return string
     */
    private static function getPattern ($format) {
        switch ($format) {
            case self::TYPE_NAME:
                return self::PATTERN_NAME;
                break;
            case self::TYPE_INTEGER:
                return self::PATTERN_INTEGER;
                break;
        }
    }
}