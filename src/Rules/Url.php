<?php

namespace Wijoc\ValidifyMI\Rules;

use Wijoc\ValidifyMI\Rule;

class UrlRule extends Rule
{
    public function validate($field, $value, $parameters): bool
    {
        if ($value == '' || $value == null || empty($value)) {
            return true;
        }

        if (strpos($field, '.') !== false || is_array($field)) {
            if (is_array($value)) {
                $checkValue = [];

                foreach($value as $key => $val) {
                    $checkValue[$key] = (filter_var($val, FILTER_VALIDATE_URL) !== false);
                }

                return in_array(false, $checkValue) ? false : true;
            }
        }

        return (filter_var($value, FILTER_VALIDATE_URL) !== false);
    }

    public function getErrorMessage($field, $parameters): string
    {
        return "The {$field} must be a valid url.";
    }
}
