<?php

namespace V\Rules;

use V\Rule;

class UrlRule implements Rule
{
    public function validate($field, $value, $parameters): bool
    {
        if (is_array($field)) {
            if (is_array($value)) {
                if (count($value) > 0) {
                    foreach ($value as $val) {
                        if (filter_var($value, FILTER_VALIDATE_URL) == false) {
                            return false;
                            break;
                        }
                    }

                    return true;
                }

                return filter_var($value, FILTER_VALIDATE_URL) !== false;
            } else {
                return filter_var($value, FILTER_VALIDATE_URL) !== false;
            }
        } else {
            if (empty($value) || $value == "" || $value == null) {
                return true;
            } else {
                return filter_var($value, FILTER_VALIDATE_URL) !== false;
            }
        }
    }

    public function getErrorMessage($field, $parameters): string
    {
        if (is_array($field)) {
            return "One of field '{$field[0]}' values must be a valid url.";
        } else {
            return "The {$field} must be a valid url.";
        }
    }
}
