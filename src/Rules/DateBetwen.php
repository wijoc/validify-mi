<?php

namespace Wijoc\ValidifyMI\Rules;

use Wijoc\ValidifyMI\RuleWithRequest;
use DateTimeImmutable;
use Exception;

class DateBetweenRule extends DateRule implements RuleWithRequest
{
    /**
     * Validatin Function
     *
     * @param Mixed $field
     * @param Mixed $value
     * @param Mixed $request -> all request payload that came
     * @param Mixed $parameters - usage : {date start},{date end},{date parameter format},{value/source format}
     * @return boolean
     */
    public function validate($field, $value, $request, $parameters): bool
    {
        if ($value == '' || $value == null || empty($value)) {
            return true;
        }

        /** Check if parameter is field or date */
        $isParameterDateA = self::validateDate($parameters[0], $parameters[2] ?? 'Y-m-d H:i:s');
        $isParameterDateB = self::validateDate($parameters[1], $parameters[2] ?? 'Y-m-d H:i:s');

        if ($isParameterDateA && $isParameterDateB) {
            $parameterStartDate = DateTimeImmutable::createFromFormat($parameters[2], $parameters[0]);
            $parameterEndDate = DateTimeImmutable::createFromFormat($parameters[2], $parameters[1]);
            
            if (strpos($field, '.') !== false || is_array($field)) {
                if (is_array($value)) {
                    $checkValue = [];

                    foreach($value as $key => $val) {
                        $valueDate  = DateTimeImmutable::createFromFormat($parameters[3], $val);

                        if (($valueDate->getTimestamp() > $parameterStartDate->getTimestamp()) && $valueDate->getTimestamp() < $parameterEndDate->getTimestamp()) {
                            $checkValue[$key] = true;
                        } else {
                            $checkValue[$key] = false;
                        }
                    }
                    
                    return in_array(false, $checkValue) ? false : true;
                }
            }
            
            $valueDate  = DateTimeImmutable::createFromFormat($parameters[3], $value);

            if (($valueDate->getTimestamp() > $parameterStartDate->getTimestamp()) && $valueDate->getTimestamp() < $parameterEndDate->getTimestamp()) {
                return true;
            } else {
                return false;
            }
        } else {
            /** If field exists */
            if (in_array($parameters[$parameters[0]], $request) && in_array($parameters[$parameters[1]], $request)) {
                /** check if field contain date or not */
                $isFieldDateA = self::validateDate($parameters[0], $parameters[2] ?? 'Y-m-d H:i:s');
                $isFieldDateB = self::validateDate($parameters[1], $parameters[2] ?? 'Y-m-d H:i:s');

                if ($isFieldDateA && $isFieldDateB) {
                    /** Parse as datetime object */
                    $parameterStartDate = DateTimeImmutable::createFromFormat($parameters[2], $parameters[0]);
                    $parameterEndDate = DateTimeImmutable::createFromFormat($parameters[2], $parameters[1]);
            
                    if (strpos($field, '.') !== false || is_array($field)) {
                        if (is_array($value)) {
                            $checkValue = [];
    
                            foreach($value as $key => $val) {
                                $valueDate  = DateTimeImmutable::createFromFormat($parameters[3], $val);
    
                                if (($valueDate->getTimestamp() > $parameterStartDate->getTimestamp()) && $valueDate->getTimestamp() < $parameterEndDate->getTimestamp()) {
                                    $checkValue[$key] = true;
                                } else {
                                    $checkValue[$key] = false;
                                }
                            }
                            
                            return in_array(false, $checkValue) ? false : true;
                        }
                    }
                    
                    $valueDate  = DateTimeImmutable::createFromFormat($parameters[3], $value);

                    if (($valueDate->getTimestamp() > $parameterStartDate->getTimestamp()) && $valueDate->getTimestamp() < $parameterEndDate->getTimestamp()) {
                        return true;
                    } else {
                        return false;
                    }
                } else {
                    throw new Exception("Field {$parameters} not comparable!");
                }
            } else {
                throw new Exception("Field {$parameters} didn't exists!");
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
        if (strpos($field, '.*') !== false) {
            return "One of the '" . substr($field, 0, -2) . "' value must between {$parameters[0]} and {$parameters[1]}.";
        } else {
            return "The {$field} must between {$parameters[0]} and {$parameters[1]}.";
        }
    }
}
