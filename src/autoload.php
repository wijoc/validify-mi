<?php

return new class
{
    public function __construct()
    {
        $this->load([
            __DIR__ . '/QueryBuilder.php',
            __DIR__ . '/Rule.php',
            __DIR__ . '/RuleWithoutOtherRules.php',
            __DIR__ . '/RuleWithRequest.php',
            __DIR__ . '/Rules/*.php',
        ]);
    }

    public function load($paths)
    {
        foreach ($paths as $path) {
            foreach (glob($path) as $file) {
                require_once $file;
            }
        }
    }
};