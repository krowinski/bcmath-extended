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
    - hexdec - converting from hexadecimal to decimal
    - dechex - converting from decimal to hexadecimal
    - bin2dec - converting from binary to decimal
    - dec2bin - converting from decimal to binary
- new math functions 
    - round
    - abs 
    - rand
    - max
    - min
    - roundDown
    - roundUp
    - ceil
    - exp
    - log
    - fact
    - pow (supports fractional)
    - mod (supports fractional + scale in php 5.6 <)
    - bitwise operators
        - bitXor
        - bitOr
        - bitAnd
- proxy for original functions (http://php.net/manual/en/book.bc.php)
- all functions supports scientific notation
- all functions are static so it can be easy replaced by this lib

Info
===
As of 7.2 float can be passed to bcmod but they don't return correct values (IMO)

I created bug for this in https://bugs.php.net/bug.php?id=76287 but it was commented as documentation issue not a bug.

```
bcmod() doesn't use floor() but rather truncates towards zero,
which is also defined this way for POSIX fmod(), so that the
result always has the same sign as the dividend.  Therefore, this
is not a bug, but rather a documentation issue.
```

But I still will use floor not truncated for mod in this lib.