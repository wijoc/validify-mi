<?php

namespace Wijoc\ValidifyMI\Rules;

use Wijoc\ValidifyMI\Rule;

class NumericRule extends Rule
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
            if (count($value) >= 1) {
                foreach ($value as $val) {
                    if (!is_numeric($val)) {
                        return false;
                        break;
                    }
                }
            }
            
            return is_numeric($value);
        } else {
            return is_numeric($value);
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
            return "One of the '" . substr($field, 0, -2) . "' value is non-numeric.";
        } else {
            return "The {$field} is non-numeric.";
        }
    }
}