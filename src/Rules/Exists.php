<?php

namespace Wijoc\ValidifyMI\Rules;

use Wijoc\ValidifyMI\Rule;
use Exception;

class ExistsRule extends Rule
{
    /**
     * Validating Function
     *
     * @param Mixed $field
     * @param Mixed $value
     * @param Mixed $parameters
     * @return boolean
     */
    public function validate($field, $value, $parameters): bool
    {
        if ($value == '' || $value == null || empty($value)) {
            return true;
        }

        if (empty($parameters)) {
            throw new Exception("parameters not provided!");
        }

        $params = explode('/', $parameters[0]);
        $table = $params[0];
        $field = $params[1];

        $condition = '=';
        if (isset($params[2])) {
            $condition = $params[2];
        }

        if (!in_array($condition, ['isnull', 'is null', 'isnotnull', 'is not null', 'like', 'in', 'notin', 'not in', '!=', '<>', '>', '>=', '<=', '='])) {
            throw new Exception("condition parameter should be one of : isnull | is null | isnotnull | is not null | like | in | notin | not in | != | <> | > | >= | <= | =");
        }
        
        if (strpos($field, '.') !== false || is_array($field)) {
            if (is_array($value)) {
                $checkValue = [];
                foreach ($value as $key => $val) {
                    $checkValue[$key] = $this->check($val, $table, $field, $condition);
                }
    
                return in_array(false, $checkValue) ? false : true;
            }

            $check = $this->check($value, $table, $field, $condition);
        } else {
            $check = $this->check($value, $table, $field, $condition);
        }

        return $check;
    }

    /**
     * Check post Function
     *
     * @param Mixed $values
     * @param String $postType
     * @param String $field
     * @param String $postStatus
     * @param Mixed $extraArguments
     * @return boolean
     */
    protected function check(Mixed $values, String $table, String $field, String $condition): bool 
    {
        if (strpos($field, ';')) {
            $field = explode(';', $field);
        }

        $databaseValue = $this->query->$table($table);
            if (is_array($field)) {
                foreach($field as $column) {
                    if (is_array($values)) {
                        $databaseValue->where($column, $condition, implode(',', $values));
                    } else {
                        $databaseValue->where($column, $condition, $values);
                    }
                }
            } else {
                $databaseValue->where($field, $condition, implode(',', $values));
            }
            
        $databaseValue->limit(1, 0)->get();
        
        return count($databaseValue) < 1 ? true : false;
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
        $parameters = is_array($parameters) ? $parameters[0] : $parameters;
        
        if (strpos($field, '.*') !== false) {
            return "One of the '" . substr($field, 0, -2) . "' value should has different value with field {$parameters}.";
        } else {
            return "The {$field} should has different value with field {$parameters}.";
        }
    }
}