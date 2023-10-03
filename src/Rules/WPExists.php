<?php

namespace V\Rules;

use V\Rule;

class WPExistsRule implements Rule
{
    public function validate($field, $value, $parameters): bool
    {
        if ($value == "" || $value == null || empty($value)) {
            return false;
        } else {
            // user/user_like

            $params     = explode('/', $parameters[0]);
            $table      = $params[0];

            $selector   = $parameters[1] ?? null; // post_id or user_id or term_id or taxonomy

            if (strpos($field, '.*') !== false) {
                $checkValue = [];
                foreach ($value as $key => $values) {
                    $checkValue[$key] = $this->check($values, $table, $type, $column);
                }

                return in_array(false, $checkValue) ? false : true;
            } else {
                $check = $this->check($value, $table, $type, $column);
            }
            return $check;
        }
    }

    public function check($value, $table, $type, $column): bool
    {
        switch ($table) {
            case 'user':
                return true;
                break;
            case 'post':
                $args = [
                    'fields'         => 'ids',
                    'posts_per_page' => 1,
                    'orderby'        => 'ID',
                    'post_type'      => $type,
                    'post_status'    => 'publish',
                    // 'post__in'       => [$value]
                ];

                switch ($column) {
                    case 'post_id':
                        $args['post__in'] = [$value];
                        break;
                    case 'post_name':
                        $args['name'] = $value;
                        break;
                }

                $databaseValue = get_posts($args);

                return count($databaseValue) < 1 ? false : true;
            case 'term':
                $checkTerm = get_term_by($column, $value, $type);
                return $checkTerm ? true : false;
        }
    }

    public function getErrorMessage($field, $parameters): string
    {
        if (strpos($field, '.*') !== false) {
            return "One of the '" . substr($field, 0, -2) . "' value didn't exists.";
        } else {
            return "The {$field} didn't exists.";
        }
    }
}
