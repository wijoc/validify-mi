<?php

namespace Wijoc\ValidifyMI\Rules;

use Wijoc\ValidifyMI\Rule;

class MinRule extends Rule
{
    /**
     * Validating Function
     *
     * @param Mixed $field
     * @param Mixed $value
     * @param Mixed $parameters
     * @return boolean
     */
    public function validate($field, $value, $parameters): bool
    {
        if ($value == '' || $value == null || empty($value)) {
            return true;
        }

        if ($value) {
            if (is_array($value)) {
                if (is_array($field)) {
                    foreach ($value as $val) {
                        if (!empty($val)) {
                            if (!(strlen($val) >= (int)$parameters[0])) {
                                return false;
                                break;
                            } else {
                                return true;
                            }
                        } else {
                            return true;
                        }
                    }
                } else {
                    if (!empty($value)) {
                        return count($value) >= (int)$parameters[0];
                    } else {
                        return true;
                    }
                }
            } else {
                return strlen($value) >= (int)$parameters[0];
            }
        } else {
            return true;
        }
    }

    /**
     * Get error message Function
     *
     * @param Mixed $field
     * @param Mixed $parameters
     * @return string
     */
    public function getErrorMessage($field, $parameters): string
    {
        if (strpos($field, '.*') !== false) {
            return "One of the '" . substr($field, 0, -2) . "' value must be at least {$parameters[0]} characters.";
        } else {
            return "The {$field} must be at least {$parameters[0]} characters.";
        }
    }
}