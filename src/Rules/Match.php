<?php

namespace Wijoc\ValidifyMI\Rules;

use Wijoc\ValidifyMI\RuleWithRequest;
use Exception;

class MatchRule implements RuleWithRequest
{
    /**
     * Validating Function
     *
     * @param Mixed $field
     * @param Mixed $value
     * @param Array $request -> all request payload that came
     * @param Mixed $parameters
     * @return boolean
     */
    public function validate($field, $value, $request, $parameters): bool
    {
        if ($value == '' || $value == null || empty($value)) {
            return true;
        }

        if (!empty($parameters)) {
            $parameters = is_array($parameters) ? $parameters[0] : $parameters;

            if (array_key_exists($parameters, $request)) {
                if (strpos($field, '.') !== false || is_array($field)) {
                    if (is_array($value)) {
                        $checkValue = [];
                        
                        foreach ($value as $key => $values) {
                            $checkValue[$key] = (string)$values === (string)$request[$parameters];
                        }
    
                        return in_array(false, $checkValue) ? false : true;
                    } else {
                        return (string)$value === (string)$request[$parameters];
                    }
                } else {
                    return (string)$value === (string)$request[$parameters];
                }
            } else {
                throw new Exception("Field {$parameters} didn't exists!");
            }
        } else {
            throw new Exception("{$parameters} not provided!");
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
        $parameters = is_array($parameters) ? $parameters[0] : $parameters;
        
        if (strpos($field, '.*') !== false) {
            return "One of the '" . substr($field, 0, -2) . "' value should has same value with field {$parameters}.";
        } else {
            return "The {$field} should has same value with field {$parameters}.";
        }
    }
}