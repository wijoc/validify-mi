<?php

namespace Wijoc\ValidifyMI\Rules;

use Wijoc\ValidifyMI\Rule;

class EmailRule extends Rule
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
        return filter_var($value, FILTER_VALIDATE_EMAIL) !== false;
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
            return "One of the '" . substr($field, 0, -2) . "' value must be a valid email addreess.";
        } else {
            return "The {$field} must be a valid email addreess.";
        }
    }
}