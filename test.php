<?php
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

// echo 'a';

require_once './src/autoload.php';

// use V;
// use V\Rule;
use V\Rules\Validator;

$data = [
    'require'           => 'required value', // valid
    'require-empty'     => "", // invalid
    'require-array'     => [1],
    // 'require-array'     => [],
    'require-1array'    => ['value-1', 'value-2'], // valid
    'require-2array'    => [ // valid, including require-2array.* is still valid
        [
            'value1-1', 'value1-2'
        ],
        [
            'value2-1', 'value2-2'
        ]
    ],
    // 'require-2array'    => [[], []], // this is valid for required, but will be invalid for require-2array.* with required rule
    // 'require-2array'    => [], // this is invalid for required
    'numeric'           => 12,
    'numeric-invalid'   => 'q',
    'numeric-str'       => '12',
    'numeric1'          => [ // this will be valid if rule is numeric1.* => ['numeric'], but will be invalid for numeric1 => ['numeric']
        1, 2, '3', 4
    ],
    'numeric2'          => [ // this will be valid if rule is numeric2.*.child => ['numeric'], but will be invalid for numeric2.* => ['numeric']
        [
            'child' => 12
        ],
        [
            'child' => 12
        ]
    ],
    // 'numeric3'          => [ // this will be valid if rule is numeric3.*.* => ['numeric'] or numeric3.child.* => ['numeric'], but will be invalid for numeric1 => ['numeric']
    //     'child' => [
    //         1, 2, 3, '4', 'asdas'
    //     ],
    //     'child2' => [
    //         6, 7, 8, '9', 10
    //     ]
    // ],
    'numeric3'          => [1],
    'numeric4'          => [1],
    'toexplode'           => '1,2,3,4,5'

];

$rules = [
    // 'require'           => ['required'],
    // 'require-array'     => ['required'],
    // 'require-array.*'   => ['required'],
    // 'require-empty'     => ['required'],
    // 'require-1array'    => ['required'],
    // 'require-2array'    => ['required'],
    // 'require-2array.*'  => ['required'],
    // 'numeric'           => ['numeric'],
    // 'numeric-invalid'   => ['numeric'],
    // 'numeric1.*'        => ['numeric'],
    // 'numeric1'          => ['numeric'],
    // 'numeric2.*.child'  => ['numeric'],
    // 'numeric2.*.*'  => ['numeric'],
    // 'numeric3.child.*'  => ['numeric'],
    'numeric3.*.*'  => ['numeric'],
    // 'numeric4'  => ['numeric'],
    // 'toexplode.*'         => ['numeric', 'explode']
];

$messages = [];

$validator = new Validator($data, $rules, $messages);
// $validator->validate();
if ($validator->validate()) {
    echo "Validation successful!";
} else {
    $errors = $validator->getErrors();
    foreach ($errors as $field => $fieldErrors) {
        echo "Errors for <b>{$field}</b>:\n<br>" . PHP_EOL;
        foreach ($fieldErrors as $error) {
            echo " - {$error}\n<br>" . PHP_EOL;
        }
        echo '<hr>';
    }
}
