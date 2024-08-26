<?php

namespace Wijoc\ValidifyMI\Rules;

use Wijoc\ValidifyMI\RuleWithRequest;
use Exception;

class GreaterThanEqualRule extends RuleWithRequest
{
    /**
     * Validatin Function
     *
     * @param string $field
     * @param mixed $value
     * @param array $request -> all request payload that came
     * @param mixed $parameters
     * @return boolean
     */
    public function validate($field, $value, $request, $parameters): bool
    {
        if ($value == '' || $value == null || empty($value)) {
            return true;
        }

        if (is_numeric($parameters[0])) {
            if (strpos($field, '.') !== false || is_array($field)) {
                if (is_array($value)) {
                    $checkValue = [];

                    foreach($value as $key => $val) {
                        $checkValue[$key] = (int)$val >= (int)$parameters[0];
                    }
                    
                    return in_array(false, $checkValue) ? false : true;
                }
            }

            return (int)$value >= (int)$parameters[0];
        } else if (in_array($parameters[0], $request)) {
            if (is_numeric($request[$parameters[0]])) {
                if (strpos($field, '.') !== false || is_array($field)) {
                    if (is_array($value)) {
                        $checkValue = [];
    
                        foreach($value as $key => $val) {
                            $checkValue[$key] = (int)$val >= (int)$request[$parameters[0]];
                        }
                        
                        return in_array(false, $checkValue) ? false : true;
                    }
                }

                return (int)$value >= (int)$request[$parameters[0]];
            } else {
                throw new Exception("Field {$parameters[0]} is not comparable!");
            }
        } else {
            throw new Exception("{$parameters[0]} is not comparable!");
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
            return "One of the '" . substr($field, 0, -2) . "' value must be greater than {$parameters[0]}.";
        } else {
            return "The {$field} must be greater than {$parameters[0]}.";
        }
    }
}
