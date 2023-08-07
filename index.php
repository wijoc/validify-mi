<?php
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

// echo 'a';

require_once './autoload.php';

use V;
use V\Rule;
use V\Rules\Validator;

$data = [
    'name' => 'John Doe',
    'email' => 'john@example.com',
    'age' => 25,
];

$rules = [
    'name' => ['required', 'min:3', 'max:50'],
    'email' => ['required', 'email'],
    'age' => ['required', 'numeric', 'min:18'],
];

$validator = new Validator($data, $rules);
if ($validator->validate()) {
    echo "Validation successful!";
} else {
    $errors = $validator->getErrors();
    foreach ($errors as $field => $fieldErrors) {
        echo "Errors for $field:\n";
        foreach ($fieldErrors as $error) {
            echo " - $error\n";
        }
    }
}
