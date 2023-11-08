<?php

namespace V\Rules;

use V\Rule;
use V\Rules\RequiredRule;
use V\Rules\EmailRule;
use V\Rules\MinRule;
use V\Rules\MaxRule;
use V\Rules\NumericRule;
use V\Rules\ExistsRule;
use V\Rules\NotExistsRule;
use V\Rules\MaxStoredRule;
use Exception;

class Validator
{
    private $data;
    private $rules;
    private $messages;
    private $sanitizer;
    private $is_sanitized;
    private $errors;
    private $ignored_rules;
    private $with_request_rules;

    public function __construct(array $data, array $rules, array $messages = [], array $sanitizer = [])
    {
        $this->data                 = $data;
        $this->rules                = $rules;
        $this->messages             = $messages;
        $this->sanitizer            = $sanitizer;
        $this->is_sanitized         = false;
        $this->errors               = [];
        $this->ignored_rules        = ['explode'];
        $this->with_request_rules   = ['date'];
    }

    public function validate()
    {
        foreach ($this->rules as $field => $rules) {
            print('<pre>' . print_r('field "' . $field . '" : ', true) . '</pre>');
            print('<pre>' . print_r('rule "' . implode(',', $rules) . '" : ', true) . '</pre>');

            $fields = [];
            $rawField = $field;

            if (strpos($field, '.') !== false) {
                $field = explode('.', $field);

                // $field = $fields[0];
                // array_shift($fields);

                // print('<pre>' . print_r('the field  : ' . $field, true) . '</pre>');
                print('<pre>' . print_r('the fields : ' . implode(', ', $field), true) . '</pre>');

                $value = $this->getValue($rawField, $field);
            } else {
                print('<pre>' . print_r('the field  : ' . $field, true) . '</pre>');
                // print('<pre>' . print_r('the fields : -', true) . '</pre>');

                $value = isset($this->data[$field]) ? $this->data[$field] : null;
            }

            print('<pre>' . 'value is ' . print_r(is_array($value) ? 'array' : 'not_array', true) . '</pre>');
            print('<pre>' . print_r($value ?? '--------------', true) . '</pre><hr>');

            foreach ($rules as $rule) {
                list($ruleName, $parameters) = $this->parseRule($rule);
                if (!in_array($ruleName, $this->ignored_rules)) {
                    $ruleInstance = $this->getRuleInstance($ruleName);

                    if (!$ruleInstance->validate($field, $value, $parameters)) {
                        $this->addError($rawField, $ruleName, $parameters);
                    }
                }
            }
        }

        return empty($this->errors);
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

        if (!array_key_exists($field, $this->data)) {
            return null;
        } else {
            $datas = $this->data[$field];
            $values = [];

            $fromArray = $keys[0] == '*' ? true : false;
            print('<pre>' . print_r($fromArray, true) . '</pre>');

            for ($i = 0; $i < count($keys); $i++) {
                if ($keys[$i] == '*') {
                    if ($fromArray) {
                        if (!is_array($datas)) {
                            /** Check for rule explode */
                            if (is_string($datas) && strpos($datas, ',') !== false && in_array('explode', $this->rules[$rawKeys])) {
                                $datas = explode(',', $datas);
                            } else {
                                $datas = $datas;
                            }
                        } else {
                            $datas = $datas;
                        }

                        if (is_array($datas)) {
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
                            $datas = $datas ?? NULL;
                        } else {
                            $datas = $datas ?? NULL;
                        }
                    } else {
                        $datas = $datas;
                        $values = $datas;
                    }

                    $fromArray = true;
                } else {
                    if (array_key_exists($keys[$i], $datas)) {
                        $values = $datas[$keys[$i]];
                        $datas = $datas[$keys[$i]];
                    } else {
                        if ($fromArray) {
                            $values = [];
                            foreach ($datas as $data) {
                                if (!is_array($data)) {
                                    if (is_string($data) && strpos($data, ',') !== false && in_array('explode', $this->rules[$rawKeys])) {
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
                }
            }

            return $values;
        }
    }

    private function parseRule($rule)
    {
        $parameters = [];
        if (strpos($rule, ':') !== false) {
            list($rule, $parameterString) = explode(':', $rule, 2);
            $parameters = explode(',', strtolower($parameterString));
        }

        return [$rule, $parameters];
    }

    private function getRuleInstance($ruleName)
    {
        switch ($ruleName) {
            case 'required':
                return new RequiredRule();
            case 'email':
                return new EmailRule();
            case 'min':
                return new MinRule();
            case 'max':
                return new MaxRule();
            case 'numeric':
                return new NumericRule();
            case 'in':
                return new In();
            case 'url':
                return new UrlRule();
            case 'exists':
                return new ExistsRule();
            case 'not_exists':
                return new NotExistsRule();
            case 'max_stored':
                return new MaxStoredRule();
            case 'wywsig':
                return new RequiredRule();
            case 'mime':
                return new MimeRule();
            case 'max_file_size':
                return new FileMaxSizeRule();
            default:
                throw new Exception("Rule '{$ruleName}' not supported.");
        }
    }

    private function addError($field, $rule, $parameters)
    {
        if ($this->messages && array_key_exists($field, $this->messages)) {
            $this->errors[$field][] = $this->messages[$field];
        } else {
            $fields = explode('.', $field);
            $message = $this->messages;

            for ($i = 0; $i < count($fields); $i++) {
                if (isset($message[$fields[$i]])) {
                    $message = $message[$fields[$i]];
                } else {
                    $message = "";
                }
            }

            if (empty($message) || is_array($message)) {
                // if (count($fields) > 0) {}
                $message = $this->getErrorMessage($fields, $rule, $parameters);
            }

            $this->errors[$field][] = $message;
        }
    }

    private function getErrorMessage($field, $rule, $parameters)
    {
        $ruleInstance = $this->getRuleInstance($rule);
        return $ruleInstance->getErrorMessage($field, $parameters);
    }

    public function errors()
    {
        return $this->errors;
    }

    public function validated()
    {
        return $this->data;
    }

    /** This point onward is function(s) for wordpress applications only. */

    /**
     * Sanitize with wordpress sanitize function
     * Will only work for wordpress app.
     *
     * @return bool true
     * @throws Exception
     */
    public function sanitize(): bool
    {
        if (function_exists('sanitize_text_field')) {

            if (!empty($this->sanitizer)) {
                foreach ($this->rules as $field => $rule) {
                    $fields = [];
                    $rawField = $field;

                    if (strpos($field, '.') !== false) {
                        $field = explode('.', $field);

                        if (array_key_exists($field[0], $this->data)) {
                            $this->data[$field[0]] = $this->sanitizeArray($rule, $this->data[$field[0]]);
                        } else {
                            $this->data[$field[0]] = null;
                        }
                    } else {
                        if (array_key_exists($field, $this->data)) {
                            $this->data[$field] = $this->doSanitize($rule, $this->data[$field]);
                        } else {
                            $this->data[$field] = null;
                        }
                    }
                }

                $this->is_sanitized = true;
            }

            return true;
        } else {
            throw new Exception("Sanitize only work on wordpress application.");
        }
    }

    public function sanitized(): array
    {
        if (!$this->is_sanitized && !empty($this->sanitizer)) {
            $this->sanitize();
        }

        return $this->data;
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
        switch ($rule):
            case 'email':
                if (function_exists('sanitize_text_field')) {
                    return sanitize_text_field($data);
                } else {
                    throw new Exception("Sanitize only work on wordpress application.");
                }
                break;
            case 'textarea':
                if (function_exists('sanitize_textarea_field')) {
                    return sanitize_textarea_field($data);
                } else {
                    throw new Exception("Sanitize only work on wordpress application.");
                }
                break;
            case 'text':
                if (function_exists('sanitize_text_field')) {
                    return sanitize_text_field($data);
                } else {
                    throw new Exception("Sanitize only work on wordpress application.");
                }
                break;
            case 'kses':
                if (function_exists('wp_kses')) {
                    return wp_kses($data, []);
                } else {
                    throw new Exception("Sanitize only work on wordpress application.");
                }
                break;
            case 'ksespost':
                if (function_exists('wp_kses_post')) {
                    return wp_kses_post($data);
                } else {
                    throw new Exception("Sanitize only work on wordpress application.");
                }
                break;
            default:
                return $data;
                break;
        endswitch;
    }
}
