# ArrayValidator

A custom library made to take away the pain of form data validation. Pretty useful for validating **$_POST** and **$_GET** arrays

## Getting started
1. PHP 5.4.x is required
2. [composer](https://getcomposer.org/download/) is required
3. Change to your working directory and run `composer require dabrahim/array-validator`


## Basic usage
```php
$constraints = array(
    'email' => (object)[
        'prettyName' => 'E-mail',
        'type' => ArrayValidator::TYPE_EMAIL,
    ],
    'firstName' => (object) [
        'prettyName' => 'Prénom',
        'type' => ArrayValidator::TYPE_NAME
    ],
    'lastName' => (object) [
        'prettyName' => 'Nom',
        'type' => ArrayValidator::TYPE_NAME
    ]
);

$a = array(
    'email' => 'john.doe@gmail.com',
    'lastName' => 'Doe',
    'firstName' => 'John'
);

try {
    $av = new ArrayValidator($a, $constraints);
    $av->validate();

    echo "Tout est OK !";

} catch (InvalidValueFormat $e) {
    echo "User error: " .$e->getMessage();

} catch (MissingKeyException $e) {
    echo "User error: " . $e->getMessage();

} catch (Exception $e) {
    echo "Developer error: " . $e->getMessage();
}
```