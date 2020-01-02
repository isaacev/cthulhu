---
title: Expressions
slug: expressions
chapter: 2
---

{% grammar expr %}

## Binary and unary operators

## Conditions

{% grammar block condition consequent alternative if_expr %}

### If-let conditions

Sometimes it's desirable to perform pattern matching on a value with a single pattern without needing the full power of the `match` expression.
For these cases, the if-let expression can be useful.

Instead of a boolean condition, it uses the `let` keyword and a single [pattern]({% link _spec/07-patterns.md %}):

```cth
let thing = Maybe::Just(123);

if let Maybe::Just(x) = thing {
  -- use the `x` value
};
```

If-let conditions _can_ be chained together although a match expression is likely to be more idiomatic and less verbose:

```cth
let thing = Maybe::Just(123);

if let Maybe::Just(42) = thing {
  "the answer"
} else if let Maybe::Just(x) = thing {
  "the value: " ++ ::Fmt::int(x)
} else {
  "has no value"
};

-- vs --

match thing {
  Maybe::Just(42) => "the answer",
  Maybe::Just(x) => "the value: " ++ ::Fmt::int(x),
  Maybe::None => "has no value",
};
```

## Loops

## Match expressions

{% grammar match_arm_handler match_arm match_expr %}

See the [Patterns chapter]({% link _spec/07-patterns.md %}) for information about how to construct patterns.
