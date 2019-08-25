# Compiler phases

## Parsing

- Find and report any syntax errors
- Build an abstract syntax tree (AST)
- Returns a `Cthulhu\AST\RootNode` object

## Linking

- _In progress_

## Analysis

- _In progress_

## Code generation

- Convert IR to PHP code
- Returns a `Cthulhu\Codegen\Builder` object that can be written as PHP code to
  a string or a file.
