<?php
namespace Dabrahim\ArrayValidator\Exceptions;


use Dabrahim\ArrayValidator\ArrayValidator;

class StringValidator {
    const TYPE_NAME = "Name TYPE";
    const TYPE_EMAIL = "Email TYPE";

    const PATTERN_NAME = '/^[a-zA-z]{2,}( [a-zA-Z]+)?$/';
    /**
     * @param $string string
     * @param $format string
     * @return bool
     */
    public static function validate ($string, $format) {
        return ($format === self::TYPE_EMAIL) ? filter_var($string, FILTER_VALIDATE_EMAIL) : preg_match(self::getPattern($format), $string);
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
        }
    }
}