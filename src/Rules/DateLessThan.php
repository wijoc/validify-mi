<?php

namespace Wijoc\ValidifyMI\Rules;

use Wijoc\ValidifyMI\RuleWithRequest;
use DateTimeImmutable;
use Exception;

class DateLessThanRule extends DateRule implements RuleWithRequest
{
    /**
     * Validatin Function
     *
     * @param Mixed $field
     * @param Mixed $value
     * @param Mixed $request -> all request payload that came
     * @param Mixed $parameters - usage : {field/date to compare},{date parameter format},{value/source format}
     * @return boolean
     */
    public function validate($field, $value, $request, $parameters): bool
    {
        if ($value == '' || $value == null || empty($value)) {
            return true;
        }

        /** Check if parameter is field or date */
        $isParameterDate = self::validateDate($parameters[0], $parameters[1] ?? 'Y-m-d H:i:s');

        if ($isParameterDate) {
            $parameters = DateTimeImmutable::createFromFormat($parameters[1], $parameters[0]);

            if (strpos($field, '.') !== false || is_array($field)) {
                if (is_array($value)) {
                    $checkValue = [];
    
                    foreach($value as $key => $val) {
                        $valueDate  = DateTimeImmutable::createFromFormat($parameters[2], $val);
                        $checkValue[$key] = $valueDate->getTimestamp() < $parameters->getTimestamp();
                    }
    
                    return in_array(false, $checkValue) ? false : true;
                }
            }

            $valueDate  = DateTimeImmutable::createFromFormat($parameters[2], $value);

            return $valueDate->getTimestamp() < $parameters->getTimestamp();
        } else {
            if (in_array($parameters[0], $request)) {
                /** check if field contain date or not */
                if (self::validateDate($request[$parameters[0]])) {
                    $dateParameters = DateTimeImmutable::createFromFormat($parameters[1], $request[$parameters[0]]);

                    if (strpos($field, '.') !== false || is_array($field)) {
                        if (is_array($value)) {
                            $checkValue = [];
            
                            foreach($value as $key => $val) {
                                $valueDate  = DateTimeImmutable::createFromFormat($parameters[2], $val);
                                $checkValue[$key] = $valueDate->getTimestamp() < $dateParameters->getTimestamp();
                            }
            
                            return in_array(false, $checkValue) ? false : true;
                        }
                    }

                    $valueDate  = DateTimeImmutable::createFromFormat($parameters[2], $value);

                    return $valueDate->getTimestamp() < $dateParameters->getTimestamp();
                } else {
                    throw new Exception("Field {$parameters[0]} not comparable!");
                }
            } else {
                throw new Exception("Field {$parameters[0]} didn't exists!");
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
            return "One of the '" . substr($field, 0, -2) . "' value must be older than {$parameters[0]}.";
        } else {
            return "The {$field} must be older than {$parameters[0]}.";
        }
    }
}
