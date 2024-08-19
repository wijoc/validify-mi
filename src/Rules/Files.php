<?php

namespace Wijoc\ValidifyMI\Rules;

use Wijoc\ValidifyMI\RuleWithRequest;

class FilesRule implements RuleWithRequest
{
    /**
     * Validating Function
     *
     * @param Mixed $field
     * @param Mixed $value
     * @param Mixed $request -> all request payload that came
     * @param Mixed $parameters
     * @return boolean
     */
    public function validate($field, $value, $request, $parameters): bool
    {        
        if (isset($_FILES[$field]) && !isset($request[$field])) {
            return true;
        } else if (!isset($_FILES[$field]) && isset($request[$field])) {
            return false;
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
            return "One of the '" . substr($field, 0, -2) . "' input value must be file(s).";
        } else {
            return "The {$field} Input must be file(s).";
        }
    }
}
