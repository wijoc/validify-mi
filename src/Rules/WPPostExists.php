<?php

namespace Wijoc\ValidifyMI\Rules;

use Wijoc\ValidifyMI\Rule;
use Exception;

class WPPostExistsRule extends Rule
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
        
        if (!function_exists('get_posts')) {
            throw new Exception("only available for wordpress project!");
        }

        $params = explode('/', $parameters[0]);
        $postType = $params[0];
        $status = $params[1];
        $column = $params[2];
        $extraArguments = NULL;
        if (isset($params[3])) {
            $extraArguments = $params[3];
        }

        if (strpos($field, '.') !== false || is_array($field)) {
            if (is_array($value)) {
                $checkValue = [];
                foreach ($value as $key => $val) {
                    $checkValue[$key] = $this->check($val, $postType, $column, $status, $extraArguments);
                }
    
                return in_array(false, $checkValue) ? false : true;
            }

            $check = $this->check($value, $postType, $column, $status, $extraArguments);
        } else {
            $check = $this->check($value, $postType, $column, $status, $extraArguments);
        }

        return $check;
    }

    /**
     * Check post Function
     *
     * @param Mixed $values
     * @param String $postType
     * @param String $column
     * @param String $postStatus
     * @param Mixed $extraArguments
     * @return boolean
     */
    protected function check(Mixed $values, String $postType, String $column, String $postStatus, Mixed $extraArguments): bool 
    {
        /** handle extra parameters / parameter for arguments */
        if (in_array($column, ['post_id', 'post_in', 'author', 'author_id', 'author_in', 'author_not_in']) && $extraArguments !== NULL) {
            $parameterValue = explode(';', $extraArguments);
        } else {
            $parameterValue = $values;
        }

        $args = [
            'fields'         => 'ids',
            'posts_per_page' => 1,
            'orderby'        => 'ID',
            'post_type'      => $postType,
            'post_status'    => $postStatus ?? 'publish'
        ];

        switch (strtolower($column)) {
            case 'post_in':
            case 'post_id':
                $args['post__in'] = is_array($parameterValue) ? $parameterValue : [$parameterValue];
                break;
            case 'post_name':
                $args['name'] = is_array($parameterValue) ? implode(' ', $parameterValue) : $parameterValue;
                break;
            case 'author':
            case 'author_id':
                $args['author'] = is_array($parameterValue) ? implode(',', $parameterValue) : $parameterValue;
                break;
            case 'author_in':
                $args['author_in'] = is_array($parameterValue) ? $parameterValue : [$parameterValue];
                break;
            case 'author_not_in':
                $args['author_not_in'] = is_array($parameterValue) ? $parameterValue : [$parameterValue];
                break;
            default: 
                throw new Exception("Coloumn must be one of : post_id | post_name | author | author_id");
                break;
        }

        $databaseValue = get_posts($args);

        return count($databaseValue) < 1 ? false : true;
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