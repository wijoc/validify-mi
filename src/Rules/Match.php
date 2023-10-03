<?php

namespace V\Rules;

use Exception;
use V\RuleWithRequest;
use WP_REST_Request;

class MatchRule implements RuleWithRequest
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
        if (is_array($field)) {
            if (is_array($value)) {
                if (count($value) > 0) {
                    foreach ($value as $val) {
                        if (!$this->check($val, $parameters, $request)) {
                            return false;
                            break;
                        }
                    }

                    return true;
                }

                return false;
            } else {
                return $this->check($value, $parameters, $request);
            }
        } else {
            if (empty($value) || $value == "" || $value == null) {
                return true;
            } else {
                return $this->check($value, $parameters, $request);
            }
        }
    }

    public function check($value, $parameters, $request)
    {
        if (!empty($parameters)) {
            $parameters = is_array($parameters) ? $parameters[0] : $parameters;

            if (array_key_exists($parameters, $request)) {
                return (string)$value === (string)$request[$parameters];
            } else {
                throw new Exception("Field {$parameters} didn't exists!");
            }
        } else {
            throw new Exception("{$parameters} not provided!");
        }
    }

    public function getErrorMessage($field, $parameters): string
    {
        $parameters = is_array($parameters) ? $parameters[0] : $parameters;
        if (is_array($field)) {
            return "One of the '{$field[0]}' should has them same value as field {$parameters}.";
        } else {
            return "The {$field} should has the same value as field {$parameters}.";
        }
    }
}
