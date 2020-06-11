# Internal representation (IR)

Once the abstract syntax tree (AST) has passed semantic analysis (exhaustion
checks, type checks, etc.) it can be converted into an IR tree. An IR tree
differs from the AST in a few ways:

- There are fewer types of IR nodes
- All symbol nodes are required to have a type and a symbol
- All expression nodes are required to have a type
- An IR tree is assumed to be free of semantic errors

## Optimizations

Once an IR tree has been constructed from an AST, a series of optimizations are
performed on the IR tree to improve the program's runtime performance.

### Inline

Finds simple functions (a body consisting of a single expression and a single
parameter) and replaces any call sites with the function body.

This reduces the number of function calls at runtime.

### Tree shaking

Finds functions and modules in the program that cannot ever be called from the
program's entry point. These functions and modules are then removed from the IR
tree.

This pass starts by building a graph of what functions call other functions. Any
functions that are marked with the `#[entry]` attribute are always reachable.
Any functions called by reachable functions are also reachable themselves. After
all reachable functions are found, any remaining functions are considered
unreachable and removed from the tree.

Next check each module starting with the most deeply nested modules. If a module
node contains no children, remove that module node. Recursively check parent
modules.

This reduces the amount of PHP code that has be generated.

### Combine calls

Finds nodes of the IR tree that look like this:

```text
foo(a)(b, c)
bar(d, e)(f, g, h)
baz(i)(null)
```

Then converts those nodes to the more simple:

```text
foo(a, b, c)
bar(d, e, f, g, h)
baz(i, null)
```

This simplifies the generation of PHP function calls.

## Arity analysis

While Cthulhu supports function currying, PHP does not. A naive way to
implement this feature in PHP is to use the PHP reflection API at every
call-site to determine the arity of the callee. This would incur a runtime
penalty for every function call.

To minimize the number of call sites that need to use the PHP reflection API,
the arity analysis pass marks each term symbol with an arity value.
Non-functions have an arity of 0. Functions have an arity depending on how the
function was defined. The arity is more detailed than just the type signature.

If the arity of a term cannot be determined, the arity of '?' is assigned. This
arity value indicates that static analysis isn't sufficient to determine how to
call the term therefore the PHP reflection API will need to be used at runtime.

For example:

```text
-- this function has an arity of '1 -> 0'
fn id(a: Str) -> Str {
  a
}

-- this function has an arity of '1 -> 1 -> 0'
fn bar(a: Str -> Str) -> (Str -> Str) {
  a
}

-- this function has an arity of '2 -> 1 -> 0'
fn bar(a: Str -> Int, b: Int) -> (Str -> Int) {
  { | x | b + 123 }
}
```

Arity values will propagate from sub-expressions to higher expressions.

For example:

```text
-- `foo` has an arity of '2 -> 0'
fn foo(a: Str, b: Str) -> Str { a }

-- `x` has an arity of '1 -> 0' because it applied 1 argument to `foo`
let x = foo("abc")

-- `y` has an arity of '0' because it applied 2 argument2 to `foo`
let y = foo("abc", "def")

-- `z` has an arity of '2 -> 0' because both branches have the same arity value
let z = if something {
  { | a, b | a ++ b }
} else {
  foo
}
```

An unknown arity most often happens in one of these scenarios:

- A function is passed into another function as a parameter. If the argument
  accepts more than one parameter, it isn't possible at compile time to know
  how to call the argument correctly.

- A branching expression (if/else, match, etc.) yields functions with not equal
  arity values from its branches. The arity pass does its best to combine like
  arity values but if any of the arity values are not equal, the entire
  expression will have an unknown arity.

For example:

```text
-- `foo` has an arity of '2 -> 0'
fn foo(a: Str, b: Str) -> Str { a }

-- `bar` has an arity of '1 -> 1 -> 0'
fn bar(a: Str) -> Str -> Str { { | b | a ++ b } }

-- `x` has an arity of '?' because both branches have different arity values. at
-- runtime the PHP reflection API will need to be used to determine how to call
-- the value stored in `x`
let x = if something {
  foo
} else {
  bar
}
```
