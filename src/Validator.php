<?php

namespace Wijoc\ValidifyMI;

require_once "autoload.php";

use Wijoc\ValidifyMI\Rule;
use Wijoc\ValidifyMI\Rules\RequiredRule;
use Wijoc\ValidifyMI\Rules\RequiredIfRule;
use Wijoc\ValidifyMI\Rules\EmailRule;
use Wijoc\ValidifyMI\Rules\MinRule;
use Wijoc\ValidifyMI\Rules\MaxRule;
use Wijoc\ValidifyMI\Rules\NumericRule;
use Wijoc\ValidifyMI\Rules\ExistsRule;
use Wijoc\ValidifyMI\Rules\NotExistsRule;
// use Wijoc\ValidifyMI\Rules\MaxStoredRule;
use Wijoc\ValidifyMI\Rules\MatchRule;
use Wijoc\ValidifyMI\Rules\NotMatchRule;
use Wijoc\ValidifyMI\Rules\CompareNumberRule;
use Wijoc\ValidifyMI\Rules\FileMaxSizeRule;
use Wijoc\ValidifyMI\Rules\FilesRule;
use Wijoc\ValidifyMI\Rules\GreaterThanRule;
use Wijoc\ValidifyMI\Rules\GreaterThanEqualRule;
use Wijoc\ValidifyMI\Rules\LessThanRule;
use Wijoc\ValidifyMI\Rules\LessThanEqualRule;
use Wijoc\ValidifyMI\Rules\InRule;
use Wijoc\ValidifyMI\Rules\MimeRule;
use Wijoc\ValidifyMI\Rules\RegexRule;
use Wijoc\ValidifyMI\Rules\UrlRule;
use Wijoc\ValidifyMI\Rules\DateRule;
use Wijoc\ValidifyMI\Rules\DateMoreThanRule;
use Wijoc\ValidifyMI\Rules\DateLessThanRule;
use Wijoc\ValidifyMI\Rules\DateBetweenRule;

use Exception;
use Wijoc\ValidifyMI\Rules\TypeIs;

class Validator
{
    private $data;
    private $sanitized;
    private $rules;
    private $messages;
    private $sanitizer;
    private $errors;
    private $ignoredRules;
    private $withRequestRules;
    private $keepOriginalParameter;
    private $parameterMightContainColon;
    private $isFileRules;
    private $finishValidation = false;
    private $isValidated = false;
    private $isSanitized = false;

    public function __construct(array $data = [], array $rules = [], array $messages = [], array $sanitizer = [])
    {
        $this->data = $data;
        $this->sanitized = [];
        $this->rules = $rules;
        $this->messages = $messages;
        $this->sanitizer = $sanitizer;
        $this->errors = [];
        $this->ignoredRules = ['explode'];
        $this->withRequestRules = ['requiredif', 'required_if', 'date', 'match', 'not_match', 'compare_number', 'greater_than', 'date', 'date_more_than', 'file', 'files', 'is_files', 'max_file_size'];
        $this->keepOriginalParameter = ['regex'];
        $this->parameterMightContainColon = ['regex'];
        $this->isFileRules = ['file', 'files', 'is_files'];
    }

    public static function make(array $data, array $rules, array $messages = [], array $sanitizer = [])
    {
        return new self($data, $rules, $messages, $sanitizer);
    }

    public function validate()
    {
        foreach ($this->rules as $field => $rules) {
            $fields = [];
            $rawField = $field;

            if (strpos($field, '.') !== false) {
                $field = explode('.', $field);

                $value = $this->getValue($rawField, $field);
            } else {
                if (isset($this->data[$field])) {
                    $value = isset($this->data[$field]) ? $this->data[$field] : null;
                } else {
                    $value = isset($_FILES[$field]) ? $_FILES[$field] : null;
                }
            }

            foreach ($rules as $rule) {
                list($ruleName, $parameters) = $this->parseRule($rule);
                if (!in_array($ruleName, $this->ignoredRules)) {
                    $ruleInstance = $this->getRuleInstance($ruleName);

                    if (in_array($ruleName, $this->withRequestRules)) {
                        $ruleValidate = $ruleInstance->validate($field, $value, $this->data, $parameters);
                    } else {
                        $ruleValidate = $ruleInstance->validate($field, $value, $parameters);
                    }

                    if (!$ruleValidate) {
                        $this->addError($rawField, $ruleName, $parameters);
                    }
                }
            }
        }

        $this->isValidated = empty($this->errors);
        $this->finishValidation = true;
        return $this->isValidated;
    }

    public function fails()
    {
        if (!$this->finishValidation) {
            $this->validate();
            return !$this->isValidated;
        }

        return !$this->isValidated;
    }

    private function getValue($rawKeys, $keys)
    {
        if (is_array($keys)) {
            $field = $keys[0];
            array_shift($keys);
        } else {
            $field = $keys;
            $keys = [$keys];
        }

        $rules = [];
        foreach ($this->rules as $property => $propertyRules) {
            foreach ($propertyRules as $rule) {
                list($rule, $parameter) = $this->parseRule($rule);
                $rules[$property][] = $rule;
            }
        }

        if (!array_key_exists($field, $this->data)) {
            /** Check if field is from files */
            if (!array_key_exists($field, $_FILES)) {
                return null;
            } else {
                return null;
            }
        } else {
            if (isset($rules[$rawKeys]) && array_intersect($rules[$rawKeys], $this->isFileRules)) {
                $datas = $_FILES[$field];
            } else {
                if (is_bool($this->data[$field])) {
                    $values = $this->data[$field] ? 'true' : 'false';
                } else if (is_numeric($this->data[$field]) && ($this->data[$field] == 0)) {
                    $values = "0";
                } else {
                    $datas = $this->data[$field];
                }
            }
            $values = [];

            if (is_array($datas)) {
                if (array_is_list($datas)) {
                    $fromArray = $keys[0] == '*' ? false : true;
                } else {
                    $fromArray = $keys[0] == '*' ? true : false;
                }
            } else {
                return $datas;
            }

            for ($i = 0; $i < count($keys); $i++) {
                if ($keys[$i] == '*') {
                    if ($fromArray) {
                        if (!is_array($datas)) {
                            /** Check for rule explode */
                            if (is_string($datas) && strpos($datas, ',') !== false && in_array('explode', $rules[$rawKeys])) {
                                $datas = explode(',', $datas);
                            } else {
                                $datas = $datas;
                            }
                        } else {
                            $datas = $datas;
                        }

                        foreach ($datas as $data) {
                            if (end($keys) == '*' && is_array($data)) {
                                foreach ($data as $value) {
                                    $values[] = $value;
                                }
                                $i++;
                            } else {
                                $values[] = $data;
                            }
                        }
                        $datas = $data ?? NULL;
                    } else {
                        $datas = $datas;
                        $values = $datas;
                    }

                    $fromArray = true;
                } else {
                    if (is_array($datas)) {
                        if (array_key_exists($keys[$i], $datas)) {
                            $values = $datas[$keys[$i]];
                            $datas = $datas[$keys[$i]];
                        } else {
                            if ($fromArray) {
                                $values = [];
                                foreach ($datas as $data) {
                                    if (!is_array($data)) {
                                        if (is_string($data) && strpos($data, ',') !== false && in_array('explode', $rules[$rawKeys])) {
                                            $values[] = explode(',', $data);
                                        } else {
                                            $values[] = $data;
                                        }
                                        $datas[] = $data;
                                    } else {
                                        /** Check if arra is multidimensional */
                                        if (count($datas) == count($datas, COUNT_RECURSIVE)) { // if not
                                            $values[] = $data;
                                            $datas[] = $data;
                                        } else {
                                            if (array_key_exists($keys[$i], $data)) {
                                                $values[] = $data[$keys[$i]];
                                                $datas[] = $data[$keys[$i]];
                                            }
                                        }
                                    }
                                }
                            } else {
                                if (array_key_exists($keys[$i], $datas)) {
                                    $values = $datas[$keys[$i]];
                                    $datas = $datas[$keys[$i]];
                                } else {
                                    $values = NULL;
                                }
                            }
                        }
                    } else {
                        $values = $datas;
                    }
                }
            }

            return $values;
        }
    }

    private function parseRule($rule)
    {
        $parameters = [];
        if (strpos($rule, ':') !== false) {
            if (in_array($rule, $this->parameterMightContainColon)) {
                $explodeRule = explode(':', $rule);
                $rule = $explodeRule[0];
                array_shift($explodeRule);
                $parameterString = implode(':', $explodeRule);
            } else {
                list($rule, $parameterString) = explode(':', $rule, 2);
            }

            if (in_array($rule, $this->withRequestRules)) {
                $parameters = explode(',', $parameterString);
            } else {
                if (in_array($rule, $this->keepOriginalParameter)) {
                    $parameters = explode(',', $parameterString);
                } else {
                    $parameters = explode(',', strtolower($parameterString));
                }
            }
        }

        return [$rule, $parameters];
    }

    private function getRuleInstance($ruleName)
    {
        switch ($ruleName) {
            case 'required':
                return new RequiredRule();
            case 'requiredif':
            case 'required_if':
                return new RequiredIfRule();
            case 'email':
                return new EmailRule();
            case 'min':
                return new MinRule();
            case 'max':
                return new MaxRule();
            case 'numeric':
                return new NumericRule();
            case 'in':
                return new InRule();
            case 'url':
                return new UrlRule();
            case 'exists':
                return new ExistsRule();
            case 'not_exists':
                return new NotExistsRule();
                // case 'max_stored':
                //     return new MaxStoredRule();
            case 'mime':
                return new MimeRule();
            case 'max_file_size':
                return new FileMaxSizeRule();
            case 'match':
                return new MatchRule();
            case 'not_match':
                return new NotMatchRule();
            case 'compare_number':
                return new CompareNumberRule();
            case 'greater_than':
            case 'gt':
                return new GreaterThanRule();
            case 'greater_than_equal':
            case 'gte':
                return new GreaterThanEqualRule();
            case 'less_than':
            case 'lt':
                return new LessThanRule();
            case 'less_than_equal':
            case 'lte':
                return new LessThanEqualRule();
            case 'is_files':
            case 'file':
            case 'files':
                return new FilesRule();
            case 'regex':
                return new RegexRule();
                // case 'must_be':
                //     return new MustBeRule();
            case 'date':
                return new DateRule();
            case 'date_more_than':
                return new DateMoreThanRule();
            case 'date_less_than':
                return new DateLessThanRule();
            case 'date_between':
                return new DateBetweenRule();
            case 'typeis':
            case 'type_is':
            case 'is':
                return new TypeIs();
            default:
                throw new Exception("Rule '{$ruleName}' not supported.");
        }
    }

    private function addError($field, $rule, $parameters)
    {
        if ($this->messages && is_array($this->messages)) {
            if (array_key_exists($field . '.' . $rule, $this->messages)) {
                $fields = explode('.', $field);
                $this->errors[$fields[0]][] = $this->messages[$field . '.' . $rule];
            } else if (array_key_exists($field, $this->messages)) {
                if (is_array($this->messages[$field]) && array_key_exists($rule, $this->messages[$field])) {
                    $this->errors[$field][] = $this->messages[$field][$rule];
                } else {
                    $fields = explode('.', $field);
                    $message = $this->messages;

                    for ($i = 0; $i < count($fields); $i++) {
                        if (isset($message[$fields[$i]])) {
                            $message = $message[$fields[$i]];
                        } else {
                            $message = "";
                        }

                        if ($i < 1) {
                            $field = $fields[$i];
                        } else {
                            $field .= '[' . $fields[$i] . ']';
                        }
                    }

                    if (empty($message) || is_array($message)) {
                        // if (count($fields) > 0) {}
                        $message = $this->getErrorMessage($field, $rule, $parameters);
                    }

                    $this->errors[$field][] = $message;
                }
            } else {
                $fields = explode('.', $field);
                $message = $this->messages;

                for ($i = 0; $i < count($fields); $i++) {
                    if (isset($message[$fields[$i]])) {
                        $message = $message[$fields[$i]];
                    } else {
                        $message = "";
                    }

                    if ($i < 1) {
                        $field = $fields[$i];
                    } else {
                        $field .= '[' . $fields[$i] . ']';
                    }
                }

                if (empty($message) || is_array($message)) {
                    // if (count($fields) > 0) {}
                    $message = $this->getErrorMessage($field, $rule, $parameters);
                }

                $this->errors[$field][] = $message;
            }
        } else {
            $fields = explode('.', $field);
            $message = $this->messages;

            for ($i = 0; $i < count($fields); $i++) {
                if (isset($message[$fields[$i]])) {
                    $message = $message[$fields[$i]];
                } else {
                    $message = "";
                }

                if ($i < 1) {
                    $field = $fields[$i];
                } else {
                    $field .= '[' . $fields[$i] . ']';
                }
            }

            if (empty($message) || is_array($message)) {
                // if (count($fields) > 0) {}
                $message = $this->getErrorMessage($field, $rule, $parameters);
            }

            $this->errors[$field][] = $message;
        }
    }

    private function getErrorMessage($field, $rule, $parameters)
    {
        $ruleInstance = $this->getRuleInstance($rule);
        return $ruleInstance->getErrorMessage($field, $parameters);
    }

    public function errors($return = null)
    {
        if ($return == 'all') {
            return $this->errors;
        } else {
            return $this;
        }
    }

    public function firstOfAll(): mixed
    {
        if ($this->errors && is_array($this->errors) && !empty($this->errors)) {
            $firstKey = array_key_first($this->errors);

            if ($this->errors[$firstKey]) {
                if (is_array($this->errors[$firstKey])) {
                    return $this->errors[$firstKey][0];
                } else {
                    return $this->errors[$firstKey];
                }
            } else {
                return null;
            }
        } else {
            return null;
        }
    }

    public function validated()
    {
        if (empty($this->errors)) {
            return $this->data;
        }

        return [];
    }

    public function sanitized()
    {
        if (empty($this->errors)) {
            return $this->sanitized;
        }

        return [];
    }

    public function sanitize()
    {
        /** Changed Sanitize start here */
        if (!empty($this->sanitizer)) {
            foreach ($this->sanitizer as $field => $rule) {
                if (is_string($rule) && $rule !== null && !empty($rule)) {
                    if (strpos($field, '.') !== false) {
                        $theField = explode('.', $field)[0];

                        if (array_key_exists($theField, $this->data)) {
                            $this->sanitized[$theField] = $this->sanitizeArray($rule, $this->data[$theField]);
                            // $this->data[$theField][] = $this->doSanitize($rule, $this->data[$theField]);
                        } else {
                            $this->sanitized[$theField] = null;
                        }
                    } else {
                        if (array_key_exists($field, $this->data)) {
                            $this->sanitized[$field] = $this->doSanitize($rule, $this->data[$field]);
                        } else {
                            $this->sanitized[$field] = null;
                        }
                    }
                } else {
                    if (array_key_exists($field, $this->data)) {
                        $this->sanitized[$field] = $this->data[$field];
                    }
                }
            }

            /** Replace original data with sanitized data */
            $this->data = $this->sanitized;
            $this->isSanitized = true;
        }
    }

    private function sanitizeArray(String $rule, mixed $data)
    {
        if (is_array($data)) {
            foreach ($data as $key => $value) {
                if (is_array($value)) {
                    $data[$key] = $this->sanitizeArray($rule, $value);
                } else {
                    $data[$key] = $this->doSanitize($rule, $value);
                }
            }
        }

        return $data;
    }

    private function doSanitize(String $rule, Mixed $data, String $parameter = '')
    {
        /** turn into html special char except email */
        if ($rule !== 'email') {
            // $data = htmlspecialchars($data);
        }

        switch ($rule):
            case 'email':
                if (function_exists('sanitize_email')) {
                    return sanitize_email($data);
                } else {
                    throw new Exception('Only work on wordpress!');
                }
                break;
            case 'textarea':
                if (function_exists('sanitize_textarea_field')) {
                    return sanitize_textarea_field($data);
                } else {
                    throw new Exception('Only work on wordpress!');
                }

                break;
            case 'text':
                if (function_exists('sanitize_text_field')) {
                    return sanitize_text_field($data);
                } else {
                    throw new Exception('Only work on wordpress!');
                }

                break;
            case 'kses':
                if (function_exists('wp_kses')) {
                    return wp_kses($data, []);
                } else {
                    throw new Exception('Only work on wordpress!');
                }

                break;
            case 'ksespost':
                if (function_exists('wp_kses_post')) {
                    return wp_kses_post($data);
                } else {
                    throw new Exception('Only work on wordpress!');
                }

                break;
            default:
                return $data;
                break;
        endswitch;
    }
}
