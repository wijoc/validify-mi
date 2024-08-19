<?php

namespace Wijoc\ValidifyMI\Rules;

use Wijoc\ValidifyMI\Rule;
use Wijoc\ValidifyMI\RuleWithRequest;
use DateTimeImmutable;

class DateRule implements RuleWithRequest
{
    /**
     * Validating Function
     *
     * @param Mixed $field
     * @param Mixed $value
     * @param Mixed $parameters
     * @return boolean
     */
    public function validate($field, $value, $request, $parameters): bool
    {
        if ($value == '' || $value == null || empty($value)) {
            return true;
        }

        if (strpos($field, '.') !== false || is_array($field)) {
            if (is_array($value)) {
                $checkValue = [];

                foreach($value as $key => $val) {
                    $checkValue[$key] = self::validateDate($val, $parameters[0]);
                }

                return in_array(false, $checkValue) ? false : true;
            }
        }

        return self::validateDate($value, $parameters[0]);
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
            return "One of the '" . substr($field, 0, -2) . "' value must be a valid date with format {$parameters[0]}.";
        } else {
            return "The {$field} must be a valid date with format {$parameters[0]}.";
        }
    }

    /**
     * Validate Date
     * 
     * @param String $date
     * @param String $format
     * @return string
     */
    protected function validateDate(String $date, String $format = 'Y-m-d H:i:s'): bool
    {
        $newDate = DateTimeImmutable::createFromFormat($format, $date);
        return $newDate && $newDate->format($format) === $date;
    }
}
