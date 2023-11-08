<?php

namespace V\Rules;

use V\Rule;

class MimeRule implements Rule
{
    public function validate($field, $value, $parameters): bool
    {
        if (is_array($field)) {
            if (!isset($_FILES[$field[0]]) || empty($_FILES[$field[0]]['name']) || $_FILES[$field[0]]['name'] == "" || $_FILES[$field[0]]['name'] == null) {
                return true;
            } else {
                return in_array(strtolower($_FILES[$field[0]]['type']), $parameters);
            }
        } else {
            if (!isset($_FILES[$field]) || empty($_FILES[$field]['name']) || $_FILES[$field]['name'] == "" || $_FILES[$field]['name'] == null) {
                return true;
            } else {
                return in_array(strtolower($_FILES[$field]['type']), $parameters);
            }
        }
    }

    public function getErrorMessage($field, $parameters): string
    {
        return "{$field} mime-type is not allowed. Allowed mime : " . implode(',', $parameters) . ".";
    }
}
