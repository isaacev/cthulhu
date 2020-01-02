---
title: Lexemes
slug: lexemes
chapter: 1
---

## Identifiers

{% grammar digit lower_letter upper_letter letter alphanumeric lower_ident upper_ident %}

The language distinguishes between identifiers that start with an upper-case letter and identifiers that start with a lower-case letter.
Upper-case identifiers are used for the names of types and modules.
Lower-case identifiers are used for the names of functions and variables.

### Reserved identifiers

The following are reserved as keywords and builtin constants by the language.
Use of these keywords and constants as identifiers will cause a syntax error.

```txt
else    false   fn
if      let     match
mod     native  true
type    use
```

## Literals

There are literal forms for strings, floating-point numbers, integer numbers, and boolean constants.

{% grammar literal %}

### String literals

{% grammar string_literal %}

String literals are wrapped in double quotes and cannot contain newline characters.
All string literals are of the type `Str`.

### Floating-point literals

{% grammar float_literal %}

Floating point literals must have 1 or more digits before the decimal point and 1 or more digits after the decimal point.
All floating point literals are of the type `Float`.

### Integer literals

{% grammar integer_literal %}

Integer literals have 1 or more digits.
There is currently no support for numeric separators or scientific notation.
All integer literals are of the type `Int`.

### Boolean literals

{% grammar boolean_literal %}

Boolean literals are expressed as either the `true` constant or the `false` constant.
All boolean literals are of the type `Bool`.

## Comments

Comments start with `--` and continue until the end of the line.
