# Cthulhu language

> A strongly-typed language that compiles into PHP

## Installation

### Using a pre-built phar

1. Download the `cthulhu.phar` executable from the [latest release](https://github.com/isaacev/cthulhu/releases/latest)
1. `chmod 755 cthulhu.phar`
1. `mv cthulhu.phar /usr/local/bin/cthulhu`

### Building from source

1. Clone this repository
1. In the repository root folder, run `composer dump-autoload`
1. Access the CLI by running `php cli/cli.php`. Or make the program available system-wide by something like ``ln -s `pwd`/bin/cthulhu.php /usr/local/bin/cthulhu``

## Example

A simple program looks like this:

```
use ::Io;

#[entry]
fn main() -> () {
  Io::println("hello world");
}
```

And compiles to the following PHP:

```php
<?php

namespace Hello {
  function main() {
    print("hello world\n");
  }
}

namespace {
  \Hello\main();
}
```

## Name capitalization

- Modules and types use PascalCase
- Function and variable names use camelCase

These capitalization rules are enforced by the language.

## TODO

- [x] Basic parsing
- [x] Variable name resolution across module boundaries
- [x] Generate and output PHP code from IR
- [x] Pretty error reporting
- [x] PHP AST rewriter library
- [x] Basic function inlining optimization
- [x] Basic dead-code elimination optimization
- [x] Constant folding optimization
- [x] Type name resolution
- [x] Generic functions
- [x] Pretty multi-line error snippets
- [x] Module dependency linearized and cycle detection
- [x] The Maybe type
- [x] The List type
- [x] Floating point numbers
- [x] Pattern matching
- [x] Recursion
- [x] Tail call optimization
- [x] Closures
- [ ] Extensible records
- [ ] Inference of record constraints
