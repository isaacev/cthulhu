# Syntax analysis

In order to facilitate the definition of custom operators with custom precedence
and associativity, parsing is conducted in a few phases with each subsequent
phase building a more complete syntax tree.

## Scanning

Take a string representing the contents of a source file and convert it into a
stream of `Char`s. Each `Char` represents a single ASCII codepoint in the
original file tagged with line and column information.

All characters in the document are converted to `Char`s, including whitespace.

## Lexing

Take the output of the scanning phase (a stream of `Char`s) and combine adjacent
`Char`s into `Token`s. Each token is tagged with the line and column information
and corresponds to one of the following subtypes:

- **Literal tokens:** One of the following kinds of primitive values:

  - **String tokens:** Double quoted string literals. Newlines characters are
    not allowed inside string literals.

  - **Boolean tokens:** Either the identifier `true` or `false`. This is the one
    case where words reserved by the language are not encoded as Ident tokens
    during lexing.

  - **Integer tokens:** 1 or more digits not followed by a `.` character.

  - **Float tokens:** 1 or more digits followed by a `.` character followed by
    0 or more digits.

- **Comment tokens:** Single-line comments from the source code. By default, the
  lexer discards these tokens automatically.

- **Error tokens:** A part of the source code that was malformed. Often because
  a string was unclosed.

- **Ident tokens:** An uppercase or lowercase word. This category of tokens
  includes words that may be reserved by the language except for `true` and
  `false` which are converted to Boolean tokens during lexing.

- **Delim tokens:** One of the following: `(){}[]`. These tokens are significant
  during the [nesting phase](#nesting).

- **Terminal tokens:** Represents the end of the source document.

- **Punct tokens:** A non-whitespace character that doesn't fit into one of the
  other token subtypes.

## Nesting

Take a flat stream of `Token`s and use balanced Delim tokens to build a token
tree.

## Shallow parsing

Because of the design decision to support custom infix operators with custom
precedence and associativity, parsing expressions becomes impossible until all
of the infix operators that are in-scope for an expression have been identified.

The shallow parsing phase parses everything higher than statements. After
shallow parsing there is also a shallow name resolution phase that loads other
libraries.

## Shallow name resolution

Determine which names (functions, types, operators) are exposed by each module.
This phase is responsible for analyzing each module's `use` declarations to
determine what other modules have been loaded into the local scope and creating
the local bindings accordingly.

## Deep parsing

Once shallow parsing and shallow name resolution have been completed, enough
information exists to know the operators exist in each scope and therefore how
to correctly parse expression precedence.

The deep parser also creates bindings for all names and operators encountered
inside of statements and expressions. The result of the deep parsing phase is a
complete syntax tree with each name and operator bound to the symbol that it
references.
