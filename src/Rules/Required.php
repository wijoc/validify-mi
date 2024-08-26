<?php

namespace Wijoc\ValidifyMI\Rules;

use Wijoc\ValidifyMI\Rule;

class RequiredRule extends Rule
{
    /**
     * Validating Function
     *
     * @param Mixed $field
     * @param Mixed $value
     * @param Mixed $parameters
     * @return boolean
     */
    public function validate($field, $values, $parameters): bool
    {
        if (empty($values)) {
            return false;
        }

        if (is_array($field)) {
            if (is_array($values)) {
                foreach ($values as $value) {
                    if (is_string($value)) {
                        if (empty(trim($value))) { return false; }
                    } else {
                        if (empty($value)) { return false; }
                    }
                }
                
                return !empty($values);
            } else if (is_string($values)) {
                return !empty(trim($values));
            } else {
                return !empty($values);
            }

            return !empty($values);
        }

        if (is_string($values)) {
            return !empty(trim($values));
        } else {
            return !empty($values);
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
        return "Field '{$field}' is required.";
    }
}