<?php

namespace V\Rules;

use Exception;
use V\Rule;

class MinRule implements Rule
{
    public function validate($field, $value, $parameters): bool
    {
        if (is_array($field)) {
            if (is_array($value)) {
                if (count($value) > 0) {
                    foreach ($value as $val) {
                        if (is_numeric($val)) {
                            if ((int)$val >= (int)$parameters[0]) {
                                return false;
                                break;
                            }
                        } else {
                            throw new Exception('Value must be a number to use this rule!');
                        }
                    }

                    return true;
                }

                return false;
            } else {
                if (is_numeric($value)) {
                    return (int)$value >= (int)$parameters[0];
                } else {
                    throw new Exception('Value must be a number to use this rule!');
                }
            }
        } else {
            if (empty($value) || $value == "" || $value == null) {
                return true;
            } else {
                if (is_numeric($value)) {
                    return (int)$value >= (int)$parameters[0];
                } else {
                    throw new Exception('Value must be a number to use this rule!');
                }
            }
        }

        return (int)$value >= (int)$parameters[0];
    }

    public function getErrorMessage($field, $parameters): string
    {
        $parameters = is_array($parameters) ? $parameters[0] : $parameters;
        if (is_array($field)) {
            return "One of the '{$field[0]}' value must be same as or more than {$parameters}.";
        } else {
            return "The {$field} value must be same as or more than {$parameters[0]}.";
        }
    }
}
