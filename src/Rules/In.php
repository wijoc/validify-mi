<?php

namespace V\Rules;

use V\Rule;

class In implements Rule
{
    public function validate($field, $value, $parameters): bool
    {
        if (is_array($field)) {
            if (is_array($value)) {
                if (count($value) > 0) {
                    foreach ($value as $val) {
                        if (!in_array($val, $parameters)) {
                            return false;
                            break;
                        }
                    }

                    return true;
                }

                return true;
            } else {
                return in_array($value, $parameters);
            }
        } else {
            if (empty($value) || $value == "" || $value == null) {
                return true;
            } else {
                return in_array($value, $parameters);
            }
        }
    }

    public function getErrorMessage($field, $parameters): string
    {
        // return "The value must be a in " . implode(",",$parameters);
        return "De veld: {$field} waarde moet een van de volgende zijn: " . implode(",", $parameters);
    }
}
