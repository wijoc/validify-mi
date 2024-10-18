<?php

namespace Wijoc\ValidifyMI\Rules;

use Wijoc\ValidifyMI\Rule;

class RegexRule extends Rule
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

        if (is_array($parameters)) {
            $parameters = implode('', $parameters);
        }
        $parameters = preg_replace('/\s+/', ' ', $parameters); // Trim multiple whitespace
        $parameters = '/' . $parameters . '/'; // add preg_match pattern

        if (strpos($field, '.*') !== false) {
            $checkValue = [];

            foreach ($value as $key => $values) {
                $checkValue[$key] = preg_match($parameters, $values);
            }

            return in_array(false, $checkValue) ? false : true;
        } else {
            return preg_match($parameters, $value);
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
            return "One of the '" . substr($field, 0, -2) . "' value didn't match the pattern.";
        } else {
            return "The {$field} didn't match the pattern.";
        }
    }
}