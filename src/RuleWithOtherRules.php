<?php

namespace Wijoc\ValidifyMI;

/**
 * Abstract Class RuleWithAnotherRules
 */
abstract class RuleWithAnotherRules
{
    public $wordpress;
    public $wpdb;
    public $query;

    public function __construct()
    {
        if ($this->checkIfWordpress()) {
            global $wpdb;
            $this->wpdb;
        } else {
            $host = isset($_ENV['CONNECTION_HOST']) ? $_ENV['CONNECTION_HOST'] : (defined('CONNECTION_HOST') ? CONNECTION_HOST : '');
            $username = isset($_ENV['CONNECTION_USERNAME']) ? $_ENV['CONNECTION_USERNAME'] : (defined('CONNECTION_USERNAME') ? CONNECTION_USERNAME : '');
            $password = isset($_ENV['CONNECTION_PASSWORD']) ? $_ENV['CONNECTION_PASSWORD'] : (defined('CONNECTION_PASSWORD') ? CONNECTION_PASSWORD : '');
            $database = isset($_ENV['CONNECTION_DATABASE']) ? $_ENV['CONNECTION_DATABASE'] : (defined('CONNECTION_DATABASE') ? CONNECTION_DATABASE : '');

            if (isset($host) && !empty($host) && isset($username) && !empty($username) && isset($password) && !empty($password) && isset($database) && !empty($database)) {
                $this->query    = new \Wijoc\QueryBuilder($host, $username, $password, $database);
            }
        }
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
