<?php

namespace Wijoc\ValidifyMI\Rules;

use Wijoc\ValidifyMI\Rule;

class RequiredRule implements Rule
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
                    if (empty($value)) { return false; }
                }
                
                return !empty($values);
            }

            return !empty($values);
        }

        return !empty($values);
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