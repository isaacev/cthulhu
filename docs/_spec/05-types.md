---
title: Types
slug: types
chapter: 5
---

{% grammar unit_type param_type named_type grouped_type list_type simple_type function_type type %}

## Equality

The language has a specific definition of type equality.

{% katexmm %}
When comparing two types, $T_0$ and $T_1$, the following rules are applied:
{% endkatexmm %}

1. blah blah blah
1. blah blah blah

## Pre-defined types

The following types are baked into the language to represent the most primitive values.
They are defined in the standard library in the `Kernel.cth` file.

### `Str` type

### `Float` type

### `Int` type

### `Bool` type

### `Maybe` type

The `Maybe` type is defined as:

```cth
type Maybe('a) =
  | Just('a)
  | None;
```

The `Maybe` type is used to represent the possible absence of a value.
This allows the `Maybe` type to be used in many of the places that other languages like Java or Ruby may use null or nil.

### `Result` type

The `Result` type is defined as:

```cth
type Result('a, 'b) =
  | Ok('a)
  | Err('b);
```

The `Result` type makes it possible to describe operations that have one type for the successful case and another type for the failure case.

While its definition might not look like much, the `Result` type is the main mechanism for handling errors.
Because the language doesn't have throwable exceptions, errors need to be handled using existing control flow structures.

## List types

## Function types

## Union types

## Type parameters

## Type inference

Type inference is the process by which type parameters are automatically assigned a type based on the use of that parameter in code.
Type inference takes place in a few places:

- Function application
- Union construction
- List creation

**An example of type inference in function application:**

```cth
fn id(thing: 'a) -> 'a {
  thing
}

fn main() -> () {
  let i: Int   = id(123);  -- this call is valid
  let f: Float = id(3.14); -- this call is also valid
}
```

In this example, an identity function `id` exists that takes a value of any type and immediately returns that value.
The function may not be very interesting but it demonstrates a use of type parameters where the output type of a function is related to its input type.
