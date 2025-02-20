<?php

namespace Wijoc\ValidifyMI\Rules;

use Wijoc\ValidifyMI\Rule;
use Exception;

class TypeIs extends Rule
{
    public function validate($field, $values, $parameters): bool
    {
        if ($values == '' || $values == null || empty($values)) {
            return true;
        }

        if (empty($parameters)) {
            throw new Exception("parameters not provided!");
        }

        $parameters = is_array($parameters) ? $parameters[0] : $parameters;


        if (is_array($field)) {
            if (is_array($values)) {
                $checkValue = [];

                foreach ($values as $key => $val) {
                    $checkValue[$key] = $this->check($val, $parameters);
                }

                return in_array(false, $checkValue) ? false : true;
            } else if (is_string($values)) {
                return $this->check($values, $parameters);
            } else {
                return $this->check($values, $parameters);
            }

            return $this->check($values, $parameters);
        } else if (strpos($field, '.') !== false) {
            if (is_array($values)) {
                $checkValue = [];

                foreach ($values as $key => $val) {
                    $checkValue[$key] = $this->check($val, $parameters);
                }

                return in_array(false, $checkValue) ? false : true;
            }
        }

        return $this->check($values, $parameters);
    }

    public function getErrorMessage($field, $parameters): string
    {
        if (strpos($field, '.') !== false) {
            $fields = explode('.', $field);
            return "One of the '{$fields[0]}' value type must be : {$parameters}.";
        } else {
            return "The {$field} type must be : {$parameters[0]}.";
        }
    }

    private function check(Mixed $value, String $type)
    {
        switch ($type) {
            case 'bool':
            case 'boolean':
                return is_bool($value);
                break;
            case 'numeric':
            case 'number':
                return is_numeric($value);
                break;
            case 'string':
            case 'str':
                return is_string($value);
                break;
            case 'array':
                return is_array($value);
                break;
            default:
                throw new Exception("Compare rule {$type} not allowed!");
        }
    }
}
