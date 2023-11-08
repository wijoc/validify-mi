<?php

namespace Validitify\Rules;

use Validitify\Rule;

class RequiredRule implements Rule
{
    public function validate($field, $value, $parameters): bool
    {
        if (is_array($field)) {
            if (is_array($value)) {
                if (count($value) > 0) {
                    foreach ($value as $val) {
                        if (empty($val)) {
                            return false;
                            break;
                        }
                    }

                    return true;
                }

                return !empty($value);
            } else {
                return !empty($value);
            }
        } else {
            return !empty($value);
        }

        // if (!empty($value)) {
        //     if (is_array($value)) {
        //         if (count($value) > 1) {
        //             $checkValue = [];
        //             foreach ($value as $val) {
        //                 $checkValue[] = !empty($val);
        //             }

        //             return in_array(false, $checkValue) ? false : true;
        //         }

        //         return !empty($value);
        //     } else {
        //         return false;
        //     }
        // }

        // return !empty($value);
    }

    public function getErrorMessage($field, $parameters): string
    {
        return "Field '{$field}' is required.";
    }
}
