# Command line interface builder

> `Cthulhu\lib\cli` contains an internal library for describing and
> parsing command line interfaces. It supports common features like
> subcommands, flag aliases, variadic arguments, and generated help
> menus.

## A basic example

The following program represents the minimum amount of code necessary
to initialize the library:

```php
use Cthulhu\lib\cli;

$prog = new cli\Program('hello', '1.0');

$prog->parse($argv);
```

## `Program`

Instances of the `Program` class represent an entire CLI and only one
should be instantiated. After instantiation, flags and subcommands can
be added to the object before finally passing the `$argv` object to the
program's `parse` method.

After the `parse` method has been called, subsequent subcommand and flag
definitions will have no effect on the CLI.

### `Program#bool_flag`

### `Program#short_circuit_flag`

### `Program#subcommand`

### `Program#callback`

### `Program#parse`

## `Subcommand`
