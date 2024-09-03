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
- [Sanitisation ](#sanitisation):
  - ['email'](#email)
  - ['textarea'](#textarea)
  - ['text'](#text)
  - ['kses'](#kses)
  - ['ksespost'](#ksespost)
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

You can install the library via Composer:

```bash
composer  require  wijoc/validify-mi
```

## Usage
Here is how you use the validation using ::make function.
Validator::make has 4 arguments :
1. Input that you want to validate.
2. Rules of validation.
3. Custom validation message.
4. Sanitizion rules. (for now only available for wordpress project)
All arguments should be an array.


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

  /** Get first error */
  $validator->errors()->firstOfAll();
} else {
  /** Validation passed */
  echo "Validation successful!";

  /** get validated data */
  $validated = $validator->validate();
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
  'address.*.postalCode' => ['required', 'numeric'],
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

  ## Explanation
  First, we prepare all the necessary parameters to initialize the validator.
  1. Input to validate :
  ```php
  /** Input Value to validate */
  $input = [
    'email' => 'user@example.com',
    'age' => 25
  ];
  ```

  2. Validation rules :
  ```php
  /** Validation Rule */
  $rules = [
    'email' => ['required', 'email'],
    'age' => ['required', 'numeric']
  ];
  ```

  3. Custom validation message :
  ```php
  $message = [
    'email.required' => "Email is required!",
    'email.email' => "Email is invalid!",
    'age.required' => "Age is required!",
    'age.numeric' => "Age must be numeric!"
  ];
  ```

  Next, we initialize it with the *Validator::make()* function using the prepared arguments.
  ```php
  /** Create validator
  * * You can add sanitizer as 4th arguments
  * * (for now it's limited to wordpress sanitize)
  */
  $validator = Validator::make($input, $rules, $message);
  ```

  Afterwards, we can check if the input is validated or not using either the *validate()* or *fails()* function:
  *validate()* will return **true** if all input __is validated__, and **false** if input __is not validated__.
  ```php
  /** Using validate() function */
  $validator->validate()
  ```
  or
  On the other hand, *fails()* will return **false** if input __is validated__, and **true** if all input __is not validated__.
  ```php
  /** Using fails function */
  $validator->fails()
  ```

  Example :
  ```php
  if ($validator->fails()) {
    /** Validation failed */
    print_r($validator->errors('all'));
  } else {
    /** Validation passed */
    echo "Validation successful!";
  }
  ```

  If the data is not validated, we can retrieve all errors using the *errors('all')* function. The __'all'__ argument is **required** to get all errors.
  ```php
  if ($validator->fails()) {
    /** Validation failed */
    print_r($validator->errors('all'));
  } else {
    /** Validation passed */
    echo "Validation successful!";
  }
  ```
  Alternatively, if you only want to get the first error, you can use the *firstOfAll()* method on top of the *errors()* function.
  **Please note**: If you use *firstOfAll()*, you don't need to add the __'all'__ argument to the *errors()* function.
  ```php
  if ($validator->fails()) {
    /** Validation failed */
    print_r($validator->errors()->firstOfAll());
    // print_r($validator->errors('all')->firstOfAll()); -> This will result an exception
  } else {
    /** Validation passed */
    echo "Validation successful!";
  }
  ```

  Finally, you can get your validated input with the *validated()* function.
  *validated()* function will return an array of input if all data is validated, or an empty array if the data is not validated.
  ```php
  if ($validator->fails()) {
    /** Validation failed */
    print_r($validator->errors()->firstOfAll());
    // print_r($validator->errors('all')->firstOfAll()); -> This will result an exception
  } else {
    /** Validation passed */
    echo "Validation successful!";

    /** get validated */
    $validated = $validator->validated();
    print_r($validated);
  }
  ```

  Additionally, if you are working on a WordPress project, you can use the sanitization feature. [Check here for the sanitization feature](#sanitisation).


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

## Sanitisation
Sanitisation currently only works for WordPress projects, so it uses WordPress' default sanitizer functions.
Usage :

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
  'address.*.postalCode' => ['required', 'numeric'],
];

$message = [
  'email.required' => "Email is required!",
  'email.email' => "Email is invalid!",
  'age.required' => "Age is required!",
  'age.numeric' => "Age must be numeric!"
];

$sanitizer = [
  'email' => 'email'
  'age' => 'text'
  'phoneNumber.*' => 'text',
  'socialMedia.facebook' => 'text',
  'address.*.province' => 'kses'
];

/** Create validator
* * You can add sanitizer as 4th arguments
* * (for now it's limited to wordpress sanitize)
*/
$validator = Validator::make($input, $rules, $message, $sanitizer);

if ($validator->fails()) {
  /** Validation failed */
  print_r($validator->errors('all'));
} else {
  /** get validated data */
  $validated = $validator->validated();

  /** get sanitized data */
  $sanitized = $validator->sanitized();
}
```

  - ## email
  This rule is using wordpress *sanitize_email()* function.
  ```php
  $sanitizer = [
    'input' => 'email'
  ];
  ```

  - ## textarea
  This rule is using wordpress *sanitize_textarea_field()* function.
  ```php
  $sanitizer = [
    'input' => 'textarea'
  ];
  ```

  - ## text
  This rule is using wordpress *sanitize_text_field()* function.
  ```php
  $sanitizer = [
    'input' => 'text'
  ];
  ```

  - ## kses
  This rule is using wordpress *wp_kses()* function.
  ```php
  $sanitizer = [
    'input' => 'kses'
  ];
  ```

  - ## ksespost
  This rule is using wordpress *wp_kses_post()* function.
  ```php
  $sanitizer = [
    'input' => 'ksespost'
  ];
  ```
