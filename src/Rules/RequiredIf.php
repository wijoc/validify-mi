<?php

namespace Wijoc\ValidifyMI\Rules;

use Wijoc\ValidifyMI\RuleWithRequest;
use Exception;

class RequiredIfRule extends RuleWithRequest
{
    /**
     * Validating Function
     *
     * @param Mixed $field
     * @param Mixed $value
     * @param Array $request -> all request payload that came
     * @param Mixed $parameters
     * @return boolean
     */
    public function validate($field, $value, $request, $parameters): bool
    {
        if (is_bool($value)) {
            $value = $value ? 'true' : 'false';
        } else if (is_numeric($value) && ($value == 0)) {
            $value = "0";
        }

        if ((!is_string($value) && !is_numeric($value) && !is_bool($value)) && empty($value)) {
            return false;
        }

        if ($value == '' || $value == null || empty($value)) {
            return true;
        }

        if (!empty($parameters)) {
            // $parameters = is_array($parameters) ? $parameters[0] : $parameters;

            if (is_array($request) && array_key_exists($parameters[0], $request)) {
                /** Check if main condition is required */
                $mainConditionIsValid = $this->_checkMainRequestCondition($parameters, $request);

                /** If main field is valid */
                if ($mainConditionIsValid) {
                    if (strpos($field, '.') !== false || is_array($field)) {
                        if (is_array($value)) {
                            $checkValue = [];

                            foreach ($value as $key => $values) {
                                $checkValue[$key] = (string)$values === (string)$request[$parameters];

                                if (is_string($value)) {
                                    $checkValue[$key] = (empty(trim($value))) ? true : false;
                                } else {
                                    $checkValue[$key] = empty($value);
                                }
                            }

                            return in_array(false, $checkValue) ? false : true;
                        } else if (is_string($value)) {
                            return !empty(trim($value));
                        } else {
                            return !empty($value);
                        }
                    } else if (is_string($value)) {
                        return !empty(trim($value));
                    } else {
                        return !empty($value);
                    }
                } else {
                    return true;
                }
            } else {
                return true;
            }
        } else {
            throw new Exception("{$parameters} not provided!");
        }
    }

    protected function _checkMainRequestCondition($parameters, $request)
    {
        /** Get parameter
         * 0 : field name
         * 1 : value to compare
         */
        $fieldName = $parameters[0];
        $fieldValues = $request[$fieldName];
        $valueToCompare = isset($parameters[1]) ? $parameters[1] : NULL;

        /** Check if field exists */
        if (strpos($fieldName, '.') !== false) {
            $fieldNames = explode('.', $fieldName);
            if ($fieldNames[1] == '*') {
                $fieldValues = $request[$fieldNames[0]];
            } else {
                $fieldValues = $request;
                foreach ($fieldNames as $field) {
                    if ($field !== '*') {
                        $fieldValues = $fieldValues[$field];
                    }
                }
            }
        }

        /** Check if values is empty */
        if (empty($fieldValues)) {
            return false;
        }

        /** if $valueToCompare is null, check required only
         * else: compare the value
         */
        if ($valueToCompare == NULL) {
            if (is_array($request[$fieldName])) {
                if (is_array($fieldValues)) {
                    foreach ($fieldValues as $value) {
                        if (is_string($value)) {
                            if (empty(trim($value))) {
                                return false;
                            }
                        } else {
                            if (empty($value)) {
                                return false;
                            }
                        }
                    }

                    return !empty($fieldValues);
                } else if (is_string($fieldValues)) {
                    return !empty(trim($fieldValues));
                } else {
                    return !empty($fieldValues);
                }

                return !empty($fieldValues);
            }

            if (is_string($fieldValues)) {
                return !empty(trim($fieldValues));
            } else {
                return !empty($fieldValues);
            }
        } else {
            if (is_array($request[$fieldName])) {
                if (is_array($fieldValues)) {
                    foreach ($fieldValues as $value) {
                        if (is_string($value)) {
                            if (empty(trim($value))) {
                                return false;
                            } else {
                                /** Handling if given parameter in boolean */
                                if (is_bool($valueToCompare)) {
                                    if ($valueToCompare) {
                                        return $value == "1" || $value == 'true';
                                    } else {
                                        return ($value == "0" || $value == 'false');
                                    }
                                } else if (is_string($valueToCompare) && $valueToCompare == 'null') {
                                    /** Handling if given parameter is NULL in string */
                                    return $value == NULL;
                                } else {
                                    return $value == $valueToCompare;
                                }
                            }
                        } else {
                            if (empty($value)) {
                                return false;
                            } else {
                                return $value == $valueToCompare;
                            }
                        }
                    }

                    return !empty($fieldValues) ? ($fieldValues == $valueToCompare) : false;
                } else if (is_bool($valueToCompare)) {
                    /** Handling if given parameter in boolean */
                    if ($valueToCompare) {
                        return !empty($fieldValues) ? ($fieldValues == "1" || $fieldValues == 'true') : false;
                    } else {
                        return !empty($fieldValues) ? ($fieldValues == "0" || $fieldValues == 'false') : false;
                    }
                } else if (is_string($valueToCompare) && $valueToCompare == 'null') {
                    /** Handling if given parameter is NULL in string */
                    return !empty($fieldValues) ? ($fieldValues == NULL) : false;
                } else {
                    return !empty($fieldValues) ? ($fieldValues == $valueToCompare) : false;
                }

                return !empty($fieldValues);
            }

            return !empty($fieldValues) ? ($fieldValues == $valueToCompare) : false;
        }
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
        $parameters = is_array($parameters) ? $parameters : [$parameters];

        if (strpos($field, '.*') !== false) {
            return "One of the '" . substr($field, 0, -2) . "' value is required when field {$parameters[0]}'s value is {$parameters[1]}.";
        } else {
            return "The {$field} is required when field {$parameters[0]}'s value is {$parameters[1]}.";
        }
    }
}
