<?php

namespace Wijoc\ValidifyMI\Rules;

use Wijoc\ValidifyMI\Rule;

class InRule implements Rule
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
                    return in_array($val, $parameters);
                    break;
                }
            }
            return in_array($value, $parameters);
        } else {
            return in_array($value, $parameters);
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
            return "One of the '" . substr($field, 0, -2) . "' value must be a one of : " . implode("|", $parameters);
        } else {
            return "The {$field} must be a one of : " . implode("|", $parameters);
        }
    }
}