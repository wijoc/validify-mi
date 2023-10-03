<?php

namespace V\Rules;

use V\Rule;

class NumericRule implements Rule
{
    public function validate($field, $value, $parameters): bool
    {
        if (is_array($field)) {
            if (is_array($value)) {
                if (count($value) > 0) {
                    foreach ($value as $val) {
                        if (!is_numeric($val)) {
                            return false;
                            break;
                        }
                    }

                    return true;
                }

                return is_numeric($value);
            } else {
                return is_numeric($value);
            }
        } else {
            if (empty($value) || $value == "" || $value == null) {
                return true;
            } else {
                return is_numeric($value);
            }
        }
    }

    public function getErrorMessage($field, $parameters): string
    {
        if (is_array($field)) {
            return "One of field '{$field[0]}' values must be a number.";
        } else {
            return "The {$field} value must be a number.";
        }
    }
}
