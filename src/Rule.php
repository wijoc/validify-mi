<?php

namespace Wijoc\ValidifyMI;

/**
 * Abstract Class Rule
 */
abstract class Rule {
    public $wordpress;
    public $wpdb;
    public $query;

    public function __construct()
    {
        if ($this->checkIfWordpress()) {
            global $wpdb;
            $this->wpdb;
        }
        
        $host = $_ENV['HOST'] ? $_ENV['HOST'] : (defined('CONNECTION_HOST') ? CONNECTION_HOST : '');
        $username = $_ENV['USERNAME'] ? $_ENV['USERNAME'] : (defined('CONNECTION_USERNAME') ? CONNECTION_USERNAME : '');
        $password = $_ENV['PASSWORD'] ? $_ENV['PASSWORD'] : (defined('CONNECTION_PASSWORD') ? CONNECTION_PASSWORD : '');
        $database = $_ENV['DATABASE'] ? $_ENV['DATABASE'] : (defined('CONNECTION_DATABASE') ? CONNECTION_DATABASE : '');
        $this->query    = new \Wijoc\QueryBuilder($host, $username, $password, $database);
    }

    abstract public function validate($field, $value, $parameters): bool;
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