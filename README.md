# Validify-MI

Standalone PHP Validation that is compatible with procedural PHP and WordPress.
This project provides a set of validation rules to simplify data validation in your projects, along with data sanitization specifically for WordPress projects.
Heavily influenced by Laravel validation and [rakit/validation](https://github.com/rakit/validation), this project originated from a personal need and is still in beta development.
All inputs are highly appreciated.

## Table of Contents

- [Features](#features)
- [Installation](#installation)
- [Usage](#usage)
- [Validation Rules](#validation-rules)
  - [Required](#required)
  - [Email](#email)
  - [URL](#url)
  - [Numeric](#numeric)
  - [Max Length](#max)
  - [Min Length](#min)
  - [Greater Than](#greater-than)
  - [Greater Than Equal](#greater-than-equal)
  - [Compare Number](#comapre-number)
  - [Less Than](#less-than)
  - [Less Than Equal](#less-than-equal)
  - [Date](#date)
  - [Date More Than](#date-more-than)
  - [Date Less Than](#date-less-than)
  - [Date Between](#date-between)
  - [Exists](#exists)
  - [Not Exists](#not-exists)
  - [Match](#match)
  - [Not Match](#not-match)
  - [Files](#file)
  - [File Max Size](#file-max-size)
  - [Mime](#mime)
  - [In](#in)
  - [Regex](#regex)
- [Contributing](#contributing)
- [License](#license)

## Features

- Simple syntax
- Supports multiple validation rules
- Compatible with customs error messages
- Compatible with procedural PHP and WordPress
- Support Wordpress sanitazion
- Support nested validation

## Installation

You can install the SDK via Composer:

```bash
composer  require  wijoc/validify-mi
```

## Usage

Basic usage of validation:

```php
<?php
require 'vendor/autoload.php';

use Wijoc\ValidifyMI\Validator;
$input = [
  'email' => 'user@example.com',
  'age' => 25
];

$rules = [
  'email' => ['required', 'email'],
  'age' => ['required', 'numeric']
];

$message = [
  'email.required' => "Email is required!",
  'email.email' => "Email is invalid!",
  'age.required' => "Age is required!",
  'age.numeric' => "Age must be numeric!"
];

/** Create validator
* * You can add sanitizer as 4th arguments
* * (for now it's limited to wordpress sanitize)
*/
$validator = Validator::make($input, $rules, $message);

if ($validator->fails()) {
  /** Validation failed */
  print_r($validator->errors('all'));
} else {
  /** Validation passed */
  echo "Validation successful!";
}
```

Also you can validate nested input with :

```php
<?php
require 'vendor/autoload.php';

use Wijoc\ValidifyMI\Validator;
$input = [
  'email' => 'user@example.com',
  'age' => 25,
  'phoneNumber' => [
    '123456789',
    '987654321'
  ],
  'socialMedia' => [
    'facebook' => 'https://facebook.com',
    'twitter' => 'https://twitter.com'
  ],
  'address' => [
    [
      'city' => 'Jakarta',
      'province' => 'DKI Jakarta',
      'postalCode' => '123'
    ]
  ]
];

$rules = [
  'email' => ['required', 'email'],
  'age' => ['required', 'numeric'],
  'phoneNumber' => ['min:1'],
  'phoneNumber.*' => ['numeric'],
  'socialMedia.facebook' => ['required'],
  'socialMedia.*.postalCode' => ['required', 'numeric'],
];

$message = [
  'email.required' => "Email is required!",
  'email.email' => "Email is invalid!",
  'age.required' => "Age is required!",
  'age.numeric' => "Age must be numeric!"
];

/** Create validator
* * You can add sanitizer as 4th arguments
* * (for now it's limited to wordpress sanitize)
*/
$validator = Validator::make($input, $rules, $message);

if ($validator->fails()) {
  /** Validation failed */
  print_r($validator->errors('all'));
} else {
  /** Validation passed */
  echo "Validation successful!";
}
```

## Validation Rules

- ## Required
  Check if given data is empty.
  ```php
  $rules = [
    'input' => ['required']
  ];
  ```
- ## email
  Check if given data is a valid email address, using PHP FILTER_VALIDATE_EMAIL
  ```php
  $rules = [
    'input' => ['email']
  ];
  ```
- ## url
  Check if given data is a valid url, using php FILTER_VALIDATE_URL
  ```php
  $rules = [
    'input' => ['url']
  ];
  ```
- ## numeric
  Check if given data is numeric.
  ```php
  $rules = [
    'input' => ['numeric']
  ];
  ```
- ## max
  Check max element of array, or total character of string. A numeric value will treated as string. Use [greater_than](#greater_than), [greater_than_equal](greater_than_equal), or [compare_number](#compare_number) to validate numeric value.
  ```php
  $rules = [
    'input' => ['max:9']
  ];
  ```
- ## min
  Check min element of array, or total character of string. A numeric value will treated as string. Use [less_than](#less_than), [less_than_equal](less_than_equal), or [compare_number](#compare_number) to validate numeric value.
  ```php
  $rules = [
    'input' => ['min:9']
  ];
  ```
- ## greater-than
  Check value is greater than given parameter. Value must be numeric.
  ```php
  $rules = [
    'input' => ['greater_than:9']
  ];
  ```
  or
  ```php
  $rules = [
    'input' => ['gt:9']
  ];
  ```
- ## greater-than-equal
  Check value is greater than or equal to given parameter. Value must be numeric.
  ```php
  $rules = [
    'input' => ['greater_than_equal:9']
  ];
  ```
  or
  ```php
  $rules = [
    'input' => ['gte:9']
  ];
  ```
- ## less-than
  Check value is less than given parameter. Value must be numeric.
  ```php
  $rules = [
    'input' => ['less_than:9']
  ];
  ```
  or
  ```php
  $rules = [
    'input' => ['lt:9']
  ];
  ```
- ## less-than-equal
  Check value is less than or equal to given parameter. Value must be numeric.
  ```php
  $rules = [
    'input' => ['less_than_equal:9']
  ];
  ```
  or
  ```php
  $rules = [
    'input' => ['lte:9']
  ];
  ```
- ## compare-number
  Check value by compare with given parameter. Value must be numeric.
  ```php
  $rules = [
    'input' => ['compare_number:{operator},{parameter to compare}']
  ];
  ```
  example :
  ```php
  $rules = [
    'input' => ['compare_number:>,9']
  ];
  ```
- ## date
  Check value is a date and in same format as parameter.
  ```php
  $rules = [
    'input' => ['date:{date format}']
  ];
  ```
  example :
  ```php
  $rules = [
    'input' => ['date:Y-m-d H:i:s']
  ];
  ```
- ## date-more-than
  Check value is a date later than given parameter. Parameter can be other input.
  ```php
  $rules = [
    'input' => ['date_more_than:{date or request field},{date format}']
  ];
  ```
  example :
  ```php
  $rules = [
    'input' => ['date_more_than:2024-12-01 01:59:59, Y-m-d H:i:s']
  ];
  ```
  or :
  ```php
  $request = [
    'inputToCompare' => '2024-01-30',
    'input' => '2024-12-31',
  ];
  $rules = [
    'input' => ['date_more_than:inputToCompare, Y-m-d']
  ];
  ```

- ## date-less-than
  Check value is a date older than given parameter. Parameter can be other input.
  ```php
  $rules = [
    'input' => ['date_less_than:{date or request field},{date format}']
  ];
  ```
  example :
  ```php
  $rules = [
    'input' => ['date_less_than:2024-12-01 01:59:59,Y-m-d H:i:s']
  ];
  ```
  or :
  ```php
  $request = [
    'inputToCompare' => '2024-01-30',
    'input' => '2024-12-31',
  ];

  $rules = [
    'input' => ['date_less_than:inputToCompare,Y-m-d']
  ];
  ```

- ## date-between
  Check value is a date is between than given parameter. Parameter can be another request input.
  ```php
  $rules = [
    'input' => ['date_between:{start date or request field},{end date or request field},{date format}']
  ];
  ```
  example :
  ```php
  $rules = [
    'input' => ['date_between:2024-12-01 01:59:59,2024-12-31 01:59:59,Y-m-d H:i:s']
  ];
  ```
  or :
  ```php
  $request = [
    'inputStart' => '2024-01-01',
    'inputEnd' => '2024-01-30',
    'input' => '2024-12-31',
  ];

  $rules = [
    'input' => ['date_between:inputStart,inputEnd,Y-m-d']
  ];
  ```

- ## exists
- ## not-exists
- ## match
  Check value is a match/exactly same with given parameter. Parameter must be another request input.
  ```php
  $rule = [
    'input' => ['match:{request field}'];
  ];
  ```
  Example :
  ```php
  $request = [
    'inputToCompare' => 'value to compare',
    'input' => 'value input'
  ];
  $rule = [
    'input' => ['match:inputToCompare'];
  ];
  ```
- ## not-match
  Check value is not match or not exactly same with given parameter. Parameter must be another request input.
  ```php
  $rule = [
    'input' => ['not_match:{request field}'];
  ];
  ```
  Example :
  ```php
  $request = [
    'inputToCompare' => 'value to compare',
    'input' => 'value input'
  ];
  $rule = [
    'input' => ['not_match:inputToCompare'];
  ];
  ```
- ## file
  Check value is a files input.
  ```php
  $rule = [
    'input' => ['files'];
  ];
  ```
  or
  ```php
  $rule = [
    'input' => ['file'];
  ];
  ```
  or
  ```php
  $rule = [
    'input' => ['is_file'];
  ];
  ```
- ## file-max-size
  Check input file size is not less than given parameter.
  ```php
  $rule = [
    'input' => ['max_file_size:{size in KB}'];
  ];
  ```
  Example :
  ```php
  $rule = [
    'input' => ['max_file_size:20'];
  ];
  ```
- ## mime
  Check input file mime is one of than given parameter. Parameter can be multiple splitted by " , " (comma)
  ```php
  $rule = [
    'input' => ['mime:{mime type}'];
  ];
  ```
  Example :
  ```php
  $rule = [
    'input' => ['mime:image/png,image/jpeg,application/pdf'];
  ];
  ```
- ## in
  Check input value is one of than given parameter. Parameter can be multiple splitted by " , " (comma)
  ```php
  $rule = [
    'input' => ['in:value1,value2'];
  ];
  ```
- ## regex
  Check input value is match regex pattern.
  ```php
  $rule = [
    'input' => ['regex:^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[!@#$%^&*()_+])(?=\S+$).*'];
  ];
  ```
