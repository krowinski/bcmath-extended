# bcmath-extended
[![Build Status](https://travis-ci.org/krowinski/bcmath-extended.svg?branch=master)](https://travis-ci.org/krowinski/bcmath-extended)
[![Code Coverage](https://scrutinizer-ci.com/g/krowinski/bcmath-extended/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/krowinski/bcmath-extended/?branch=master)
[![Latest Stable Version](https://poser.pugx.org/krowinski/bcmath-extended/v/stable)](https://packagist.org/packages/krowinski/bcmath-extended) 
[![Total Downloads](https://poser.pugx.org/krowinski/bcmath-extended/downloads)](https://packagist.org/packages/krowinski/bcmath-extended) 
[![Latest Unstable Version](https://poser.pugx.org/krowinski/bcmath-extended/v/unstable)](https://packagist.org/packages/krowinski/bcmath-extended) 
[![License](https://poser.pugx.org/krowinski/bcmath-extended/license)](https://packagist.org/packages/krowinski/bcmath-extended)

Extends php BCMath lib for missing functions like floor, ceil, round, abs, min, max, rand for big numbers.
Also wraps existing BCMath functions. (more http://php.net/manual/en/book.bc.php)

Installation
===

```sh
composer require krowinski/bcmath-extended
```

Features
===

- new tool methods
    - convertScientificNotationToString - converts scientific notation to string
    - getScale - gets current global scale 
    - getDecimalsLengthFromNumber - gets amount of decimals 
- new math functions 
    - round
    - abs 
    - rand
    - max
    - min
    - roundDown
    - roundUp
    - ceil
    - fmod
    - exp
    - log
    - fact
    - pow (now supports fractional)
- proxy for original functions (http://php.net/manual/en/book.bc.php)
- all functions supports scientific notation
- all functions are static so it can be easy replaced by this lib
