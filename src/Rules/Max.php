<?php

namespace Wijoc\ValidifyMI\Rules;

use Wijoc\ValidifyMI\Rule;

class MaxRule extends Rule
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

        if (is_array($value)) {
            if (!empty($value)) {
                return count($value) <= (int)$parameters[0];
            } else {
                return true;
            }
        } else {
            return strlen($value) <= (int)$parameters[0];
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
            return "One of the '" . substr($field, 0, -2) . "' value may not be greater than {$parameters[0]} characters.";
        } else {
            return "The {$field} may not be greater than {$parameters[0]} characters.";
        }
    }
}