<?php

namespace Wijoc\ValidifyMI;

/**
 * Abstract Class RuleWithRequest
 */
abstract class RuleWithRequest {
    public $wordpress;
    public $wpdb;
    public $query;

    public function __construct()
    {
        if ($this->checkIfWordpress()) {
            global $wpdb;
            $this->wpdb;
        }
        
        $this->query    = new \Wijoc\QueryBuilder();
    }

    abstract public function validate($field, $value, $request, $parameters): bool;
    abstract public function getErrorMessage($field, $parameters): string;

    /**
     * Check if current project is wordpress function
     *
     * @return bool
     */
    private function checkIfWordpress(): bool
    {
        $directory = $_SERVER['DOCUMENT_ROOT'];
        if (file_exists($directory . '/wp-config.php')) {
            if (is_dir($directory . '/wp-includes')) {
                if (is_dir($directory . '/wp-admin')) {
                    if (is_dir($directory . '/wp-content')) {
                        $this->wordpress = true;
                        return $this->wordpress;
                    }
                }
            }
        }

        $this->wordpress = false;

        return $this->wordpress;
    }
}