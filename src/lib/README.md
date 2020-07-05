# Utility libraries

This directory contains cross-purpose libraries used throughout the language
pipeline. Code should only be moved into this directory if it is:

- Self-contained
- Accomplishing a specific task
- Used across the larger language modules

A brief description about each library:

## Command line argument parser

Located in `./cli`

One of the main interfaces for the compiler is via the command line. The command
line argument parser makes it easy to describe the subcommands, flags, and
arguments supported by the compiler. The library will automatically generate
help commands using the description. The library also exposes a tab-completion
that respects the description.

## Graph cycle detector

Located in `./cycle`

While the cycle detection algorithm is only used by the linker, the algorithm is
self-contained and generic.

## Debug mode

Located in `./debug`

Many compiler commands can be run in debug mode. Debug mode displays more
diagnostic information and is useful for investigating compiler bugs. This
library exposes methods for determining if debug mode should be activated and
methods for checking if debug mode is activated or not.

## Line-based diff generation

Located in `./diff`

When compiler tests fail, this library creates a report to highlight the
differences between the expected and found outputs.

## Text formatting and report generation

Located in `./fmt`

Many parts of the compiler generate text output either in the form of help
messages (the CLI library), error reports, or generated code. This library
abstracts text report generation to make it easy to toggle ANSI colors, column
indentation, and report concatenation.

The library uses a monad-like pattern to treat reports as a sequence of output
instructions (see the `Builder` class in `./fmt/Builder.php`). This defers the
modification of the STDOUT or STDERR streams until the report is finalized.

When a `Builder` object is ready for output, it is passed a `Formatter` object
(see `./fmt/Formatter.php`). There are different `Formatter` classes for streams
and strings depending on how the compiler wants to output the data.

Given the number of places in the compiler that generate formatted text reports,
the abstractions in this library have proved to be very valuable.

## Panic handling for internal compiler errors

Located in `./panic`

## Compiler test runner

Located in `./test`

## Tree walking and immutable editing

Located in `./trees`

Many parts of the compiler need to search trees to find or edit certain nodes.
Examples include the type-checker (walks the AST tree), the IR optimizer (edits
the IR tree), and the PHP optimizer (edits the PHP tree).

This library exposes a set of abstractions for describing the structure of
nodes within the different kinds of trees in the compiler. The common node
descriptions enables this library to define a `Visitor` class (see
`./trees/Visitor.php`) that can walk or edit any kind of tree in the compiler.

It's important to note that when the `Visitor` is editing a tree, those edits
are always creating a new tree that reuses any un-modified nodes from the
original tree. The abstractions in this library have made it trivial to walk
and edit arbitrary kinds of trees and thus have proven to be very valuable.
