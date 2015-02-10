#Phimple
[![Build Status](https://img.shields.io/travis/Strident/Phimple.svg)](https://travis-ci.org/Strident/Phimple) 
[![Coverage](https://img.shields.io/codeclimate/coverage/github/Strident/Phimple.svg)](https://codeclimate.com/github/Strident/Phimple)
[![Code Climate](https://img.shields.io/codeclimate/github/Strident/Phimple.svg)](https://codeclimate.com/github/Strident/Phimple)

Phimple is an extremely lightweight dependency injection container, heavily inspired by [Pimple][1].

##Installation

Phimple is available as a Composer package. It can be included in your project by running the following command:

```
$ composer require strident/phimple ~2.0
```

##Usage

Phimple is easy to get going with (just like Pimple really):

```php
use Phimple\Container;

$container = new Container();
```

Phimple differs from Pimple internally in some ways, and also in it's API. The API change was the main reason I decided to develop Phimple. There are 2 concepts; **services** and **parameters**.

[1]: https://github.com/silexphp/Pimple

