<?php

namespace V\Rules;

use V\Rule;

class RegexRule implements Rule
{
    public function validate($field, $value, $parameters): bool
    {
        if (is_array($field)) {
            if (is_array($value)) {
                if (count($value) > 0) {
                    foreach ($value as $val) {
                        if (!preg_match($parameters, $val)) {
                            return false;
                            break;
                        }
                    }

                    return true;
                }

                return false;
            } else {
                return preg_match($parameters, $value);
            }
        } else {
            if (empty($value) || $value == "" || $value == null) {
                return true;
            } else {
                return preg_match($parameters, $value);
            }
        }
    }

    public function getErrorMessage($field, $parameters): string
    {
        if (is_array($field)) {
            return "One of the '{$field[0]}' value didn't match the pattern.";
        } else {
            return "The {$field} didn't match the pattern.";
        }
    }
}
