<?php

namespace Wijoc\ValidifyMI\Rules;

use Wijoc\ValidifyMI\RuleWithRequest;
use Exception;

class CompareNumberRule implements RuleWithRequest
{
    /**
     * Validatin Function
     *
     * @param [string] $field
     * @param [mixed] $value
     * @param [array] $request -> all request payload that came
     * @param [mixed] $parameters
     * @return boolean
     */
    public function validate($field, $value, $request, $parameters): bool
    {
        if ($value == '' || $value == null || empty($value)) {
            return true;
        }

        if (is_numeric($parameters[1])) {
            if (strpos($field, '.') !== false || is_array($field)) {
                if (is_array($value)) {
                    $checkValue = [];

                    foreach($value as $key => $val) {
                        $checkValue[$key] = $this->doComparison($parameters[0], $val, $parameters[1]);
                    }
                    
                    return in_array(false, $checkValue) ? false : true;
                }
            }

            return $this->doComparison($parameters[0], $value, $parameters[1]);
        } else {
            if (array_key_exists($parameters[1], $request)) {
                if (is_numeric($request[$parameters[1]])) {
                    if (strpos($field, '.') !== false || is_array($field)) {
                        if (is_array($value)) {
                            $checkValue = [];
        
                            foreach($value as $key => $val) {
                                $checkValue[$key] = $this->doComparison($parameters[0], $val, $request[$parameters[1]]);
                            }
                            
                            return in_array(false, $checkValue) ? false : true;
                        }
                    }

                    return $this->doComparison($parameters[0], $value, $request[$parameters[1]]);
                } else {
                    throw new Exception("Field {$parameters} not comparable!");
                }
            } else {
                throw new Exception("Field {$parameters[1]} didn't exists!");
            }
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
        return "The {$field} must be greater than {$parameters[0]}.";
    }

    /**
     * Do validation Function
     *
     * @param String $operator
     * @param Mixed $value
     * @param Mixed $parameters
     * @return string
     */
    private function doComparison(String $operator, Mixed $value, Mixed $parameter): bool 
    {
        switch ($operator) {
            case '=':
                return (int)$value == (int)$parameter;
                break;
            case '<':
                return (int)$value < (int)$parameter;
                break;
            case '>':
                return (int)$value > (int)$parameter;
                break;
            case '<=':
                return (int)$value <= (int)$parameter;
                break;
            case '>=':
                return (int)$value >= (int)$parameter;
                break;
            case '<>':
                return (int)$value !== (int)$parameter;
                break;
            default:
                throw new Exception("Compare rule {$operator} not allowed!");
        }
    }
}