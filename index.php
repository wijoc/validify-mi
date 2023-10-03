<?php
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

// echo 'a';

require_once './autoload.php';

// use V;
// use V\Rule;
use V\Rules\Validator;

$data = [
    'name' => 'John Doe',
    'email' => 'john@example.com',
    'age' => 25,
    'test1' => [
        'value1-1',
        'value1-2',
        'value1-3'
    ],
    'test2i' => [
        [
            'gc1' => 'value-gc1-1.1-2i',
            'gc2' => 'value-gc2-1.2-2i',
            'gc3' => 'value-gc3-1.3-2i',
        ],
        [
            'gc1' => 'value-gc1-2.1-2i',
            'gc2' => 'value-gc2-2.2-2i',
            // 'gc3' => 'value-gc3-2.3-2i',
            'gc3' => NULL,
        ]
    ],
    'test2k' => [
        'a' => [
            'gc1' => 'value-gc1-1.1-2k-a',
            'gc2' => 'value-gc2-1.2-2k-a',
            'gc3' => 'value-gc3-1.3-2k-a',
        ],
        'b' => [
            'gc1' => 'value-gc1-2.1-2k-b',
            'gc2' => 'value-gc2-2.2-2k-b',
            'gc3' => 'value-gc3-2.3-2k-b',
        ]
    ],
    'test2k' => [
        'a' => [
            'gc1' => 'value-gc1-1.1-2k-a',
            'gc2' => 'value-gc2-1.2-2k-a',
            'gc3' => 'value-gc3-1.3-2k-a',
        ],
        'b' => [
            'gc1' => 'value-gc1-2.1-2k-b',
            'gc2' => 'value-gc2-2.2-2k-b',
            'gc3' => 'value-gc3-2.3-2k-b',
            'gc4' => [
                'a' => 'gc4-value'
            ],
            'gc5' => [
                'gc5-value-1',
                'gc5-value-2',
                'gc5-value-3'
            ]
        ]
    ]
];

$rules = [
    // 'name' => ['required', 'min:3', 'max:50'],
    // 'email' => ['required', 'email'],
    // 'age' => ['required', 'numeric', 'min:18'],
    // 'test1.*' => ['required'],
    // 'test2i.*' => ['required'], // check only first child
    // 'test2i.*.gc1' => ['required'], // check all child
    // 'test2i.*.gc3' => ['required'], // check all child
    // 'test2k.a.gc3' => ['required'], // check only 'a' child
    // 'test2k.a' => ['required'], // check only 'a' child, but last one is to get all value
    // 'test2k.a.*' => ['required'], // check only 'a' child, but last one is to get all value
    // 'test2k.c.gc3' => ['required'], // check only 'c' child, but c didn't exists
    // 'test2k.b.gc4.a' => ['required'], // check $_POST['test2k']['b']['gc4']['a'] value
    // 'test2k.b.gc4' => ['required'], // check $_POST['test2k']['b']['gc4'] all values
    // 'test2k.b.gc4.*' => ['required'], // check only $_POST['test2k']['b']['gc4'] all values
    // 'test2k.b.*.*' => ['required'], // check all value in $_POST['test2k']['b']['gc5']
    // 'test2k.b.gc5' => ['required'], // check all value in $_POST['test2k']['b']['gc5']
];

$messages = [
    // 'name' => 'Field "name" - custom message',
    // 'email' => 'Field "email" - custom message',
    // 'age' => 'Field "age" - custom message',
    // 'test1.*' => 'Field "test1.*" - custom message',
    // 'test2i.*' => 'Field "test2i.*" - custom message',
    // 'test2i.*.gc1' => 'Field "test2i.*.gc1" - custom message',
    // 'test2i.*.gc3' => 'Field "test2i.*.gc3" - custom message',
    // 'test2k.a.gc3' => 'Field "test2k.a.gc3" - custom message',
    // 'test2k.a' => 'Field "test2k.a" - custom message',
    // 'test2k.a.*' => 'Field "test2k.a.*" - custom message',
    // 'test2k.c.gc3' => 'Field "test2k.c.gc3" - custom message',
    // 'test2k.b.gc4.a' => 'Field "test2k.b.gc4.a" - custom message',
    // 'test2k.b.gc4' => 'Field "test2k.b.gc4" - custom message',
    // 'test2k.b.gc4.*' => 'Field "test2k.b.gc4.*" - custom message',
    // 'test2k.b.*.*' => 'Field "test2k.b.*.*" - custom message',
    // 'test2k.b.gc5' => 'Field "test2k.b.gc5" - custom message',
    // 'test2k' => [
    //     'c' => [
    //         'gc3' => 'Field "test2k.c.gc3" - custom message',
    //     ]
    // ],
];

$validator = new Validator($data, $rules, $messages);
// $validator->validate();
if ($validator->validate()) {
    echo "Validation successful!";
} else {
    $errors = $validator->getErrors();
    foreach ($errors as $field => $fieldErrors) {
        echo "Errors for $field:\n<br>" . PHP_EOL;
        foreach ($fieldErrors as $error) {
            echo " - $error\n<br>" . PHP_EOL;
        }
    }
}
