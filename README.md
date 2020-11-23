Validation
==========

This is a fork of [overtrue/validation](https://github.com/overtrue/validation), with several changes and additions.

This validation library is designed to make it more convenient for you to complete data validation in any project or framework.

# Usage

```php
<?php

use Qubus\Validation\Translators\DefaultTranslator;
use Qubus\Validation\Factory as ValidatorFactory;

// Initialize the factory object.
$factory = new ValidatorFactory(new DefaultTranslator);


// Verify the following fields.
$rules = [
    'username' => 'required|min:5',
    'password' => 'confirmed',
    ///...
];

$validator = $factory->make($input, $rules);

// Check if the fields passed verification.
if ($validator->passes()) {
    // If passed, do something.
} else {
    // If did not pass, print all errors. For the first error: $validator->messages()->first()
    print_r($validator->messages()->all()); // or $validator->messages()->first() or $validator->errors()
}

```

## Custom Messages in Your Language：

Take Spanish as an example：

```php
$messages = [
    'accepted'             => 'Se debe aceptar el :attribute.',
    'active_url'           => 'El :attribute no es una URL válida.',
    'after'                => 'El :attribute debe ser una fecha posterior a :date.',
    'alpha'                => 'El :attribute solo puede contener letras.',
    'alpha_dash'           => 'El :attribute solo puede contener letras, números y guiones.',
    'alpha_num'            => 'El :attribute solo puede contener letras y números.',
    // ...
];

// Pass the message array into the default translator class.
$factory = new ValidatorFactory(new DefaultTranslator($messages));

```

## Set Attribute Name

```php
$attributes = [
    'username' => 'username',
    'password' => 'password',
];

$rules = [
    'username' => 'required|min:5',
    'password' => 'confirmed',
    ///...
];

$messages = [...]; // Custom message, if you have set the message when initializing the factory, just leave it blank.

$validator = $factory->make($input, $rules, $messages, $attributes);
```

# License

MIT [License](https://opensource.org/licenses/MIT).
