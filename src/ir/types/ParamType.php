<?php

namespace Cthulhu\ir\types;

use Cthulhu\ir\names;
use Cthulhu\ir\nodes;

class ParamType extends Type {
  public names\TypeSymbol $symbol;
  public nodes\Name $name;
  public ?Type $binding;

  function __construct(names\TypeSymbol $symbol, nodes\Name $name, ?Type $binding) {
    $this->symbol = $symbol;
    $this->name = $name;
    $this->binding = $binding;
  }

  function unwrap(): Type {
    if ($this->binding) {
      return $this->binding->unwrap();
    }
    return $this;
  }

  function accepts_as_parameter(Type $other): bool {
    if ($this->binding) {
      return $this->binding->accepts_as_parameter($other->unwrap());
    }
    return true;
  }

  function accepts_as_return(Type $other): bool {
    if ($this->binding) {
      return $this->binding->accepts_as_return($other);
    } else if ($other->unwrap() instanceof self) {
      $other = $other->unwrap();
      return $this->symbol->equals($other->symbol);
    }
    return false;
  }

  function unify(Type $other): ?Type {
    if ($this->binding) {
      if ($unification = $this->binding->unify($other)) {
        return new self($this->symbol, $this->name, $unification);
      }
    } else if ($other instanceof self) {
      if ($this->symbol->equals($other->symbol)) {
        return $this;
      }
    } else {
      return $other;
    }
    return null;
  }

  function bind_parameters(array $replacements): Type {
    if (array_key_exists($this->symbol->get_id(), $replacements)) {
      $new_binding = $replacements[$this->symbol->get_id()];
      return new self($this->symbol, $this->name, $new_binding);
    }
    if ($this->binding) {
      return $this->binding->bind_parameters($replacements);
    }
    return $this;
  }

  function __toString(): string {
    if ($this->binding) {
      return "$this->binding";
    } else {
      return "'$this->name";
    }
  }
}
