# Code generation

## Yield strategies

Yield strategies determine whether a branching statement should return, assign,
or ignore the last expression in each of its branches.

Yield strategies are stored in a stack. It is valid to copy the current yield
strategy and push it onto the stack again if the last statement in a branch is
another branching statement. The yield strategy is popped from the stack when
the branch statement is exited.

When entering a function or closure definition, push the return strategy onto
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

### Examples

**The return strategy:**

```php
if ($something) {
  return $a + 4;
} else {
  return $b + 5;
}
```

**The assign strategy:**

```php
if ($something) {
  $result = $a + 4;
} else {
  $result = $b + 5;
}
```

**The ignore strategy:**

```php
if ($something) {
  $a + 4;
} else {
  $b + 5;
}
```

An important feature of yield strategies is that they propogate to nested
branching statements if necessary:

```php
if ($something) {
  return $a + 4;
} else {
  if ($something_else) {
    return $b + 5;
  } else {
    return $c + 6;
  }
}
```
