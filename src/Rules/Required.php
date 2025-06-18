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
        if (is_bool($values)) {
            $values = $values ? 'true' : 'false';
        } else if (is_numeric($values) && ($values == 0)) {
            $values = "0";
        } else if (is_string($values) && ($values == false)) {
            $values = "0";
        }

        if ((!is_string($values) && !is_numeric($values) && !is_bool($values)) && empty($values)) {
            return false;
        }

        if (is_array($field)) {
            if (is_array($values)) {
                foreach ($values as $value) {
                    if (is_string($value)) {
                        return !(trim($value) == "");
                    } else {
                        if (empty($value)) {
                            return false;
                        }
                    }
                }

                return !empty($values);
            } else if (is_string($values)) {
                return !(trim($values) == "");
            } else {
                return !empty($values);
            }

            return !empty($values);
        }

        if (is_string($values)) {
            // return !empty(trim($values));
            return !(trim($values) == "");
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
