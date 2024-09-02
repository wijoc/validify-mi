<?php

namespace Wijoc\ValidifyMI\Rules;

use Wijoc\ValidifyMI\Rule;

class MimeRule extends Rule
{
    public function validate($field, $value, $parameters): bool
    {
        $theField = strpos($field, '.*') !== false ? explode('.*', $field)[0] : $field;

        if (!array_key_exists($theField, $_FILES) || (is_array($_FILES[$theField]['name']) && count($_FILES[$theField]['name']) <= 0)) {
            return true;
        }

        /** if field contain "." */
        if (strpos($field, '.') !== false) {
            $checkValue = [];
            foreach ($value as $key => $values) {
                /** Insecure way to compare type
                 * the type input might be altered by attacker.
                 */
                // $checkValue[$key] = in_array(strtolower($_FILES[$theField][$key]['type']), $parameters);

                /** Get MIME type. */
                $valueMimeType = mime_content_type($_FILES[$theField][$key]['tmp_name']);
                $checkValue[$key] = in_array($valueMimeType, $parameters);
            }

            return in_array(false, $checkValue) ? false : true;
        }

        return in_array(strtolower($_FILES[$theField]['type']), $parameters);
    }

    public function getErrorMessage($field, $parameters): string
    {
        return "{$field} mime-type is not allowed. Allowed mime : " . implode(',', $parameters) . ".";
    }
}
