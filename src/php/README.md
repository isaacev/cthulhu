# Code generation

## Yield strategies

Yield strategies determine whether a branching statement should return, assign,
or ignore the last expression in each of its branches.

Yield strategies are stored in a stack. It is valid to copy the current yield
strategy and push it onto the stack again if the last statement in a branch is
another branching statement. The yield strategy is popped from the stack when
the branch statement is exited.

When entering a function or closure definition, push the `return` strategy onto
the stack. Pop the `return` strategy when exiting the definition.

The rules for choosing a yield strategy are:

- If the branch statement's parent node is a `ir\Ret` node, copy the current
  yield strategy.

- If the branch statement's parent node is a `ir\Pop` node, push the `ignore`
  strategy.

- If the branch statement's parent node is a `ir\Let` node, push the `assign`
  strategy and use the let binding as the assignee.

- If the branch statement's parent node is an `ir\Expr` node, push the `assign`
  strategy and allocate a temporary variable as the assignee.

Yield strategies reduce the number of intermediate variables that have to be
generated to preserve the meaning of a program when transforming high-level
expressions into lower-level PHP statements. When many intermediate variables
are generated, the PHP output becomes very noisy and aggressive optimizations
are required to eliminate intermediate variables created during compilation.

The easier solution (though it does make the compiler a bit more complex) is to
avoid having to create intermediate variables in the first place. Yield
strategies are the way that this goal is achieved.

### Examples

**The `return` strategy:**

```text
-- imagine this is the last expression in a function body
if something {
  a + 4
} else {
  b + 5
}
```

Compiles to:

```php
if ($something) {
  return $a + 4;
} else {
  return $b + 5;
}
```

**The `assign` strategy:**

```text
let result = if something {
  a + 4
} else {
  b + 5
};
```

Compiles to:

```php
if ($something) {
  $result = $a + 4;
} else {
  $result = $b + 5;
}
```

**The `ignore` strategy:**

```text
if something {
  a + 4
} else {
  b + 5
};
```

Compiles to:

```php
if ($something) {
  $a + 4;
} else {
  $b + 5;
}
```

### Propagation

An important feature of yield strategies is that they propagate to nested
branching statements:

```text
let result = if something {
  a + 4
} else {
  if something_else {
    b + 5
  } else {
    c + 6
  }
};
```

Compiles to:

```php
if ($something) {
  $result = $a + 4;
} else {
  if ($something_else) {
    $result = $b + 5;
  } else {
    $result = $c + 6;
  }
}
```
