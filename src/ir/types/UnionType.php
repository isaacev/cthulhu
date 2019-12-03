<?php

namespace Cthulhu\ir\types;

use Cthulhu\ir\names\RefSymbol;
use Cthulhu\ir\nodes\Ref;

class UnionType extends Type implements TypeSupportingParameters {
  public RefSymbol $symbol;
  public Ref $ref;
  public array $params;
  public array $variants;

  /**
   * @param RefSymbol $symbol
   * @param Ref $ref
   * @param ParamType[] $params
   * @param VariantFields[] $variants
   */
  function __construct(RefSymbol $symbol, Ref $ref, array $params, array $variants) {
    $this->symbol = $symbol;
    $this->ref = $ref;
    $this->params = $params;
    $this->variants = $variants;
  }

  function total_parameters(): int {
    return count($this->params);
  }

  /**
   * @param Type[] $replacements
   * @return Type
   */
  function bind_parameters(array $replacements): Type {
    $new_params = [];
    foreach ($this->params as $index => $param_type) {
      $new_params[$index] = $param_type->bind_parameters($replacements);
    }

    $new_variants = [];
    foreach ($this->variants as $variant_name => $variant_fields) {
      $new_variants[$variant_name] = $variant_fields->bind_parameters($replacements);
    }

    return new self($this->symbol, $this->ref, $new_params, $new_variants);
  }

  function has_variant_named(string $name): bool {
    return isset($this->variants[$name]);
  }

  function get_variant_fields(string $name): VariantFields {
    return $this->variants[$name];
  }

  function accepts_as_parameter(Type $other): bool {
    if (self::matches($other) === false) {
      return false;
    }

    $other = $other->unwrap();
    if ($this->symbol->equals($other->symbol)) {
      for ($i = 0; $i < count($this->params); $i++) {
        if ($this->params[$i]->accepts_as_parameter($other->params[$i]) === false) {
          return false;
        }
      }
      return true;
    }

    return false;
  }

  function unify(Type $other): ?Type {
    if (self::matches($other) === false) {
      return null;
    }

    $other = $other->unwrap();
    if ($this->symbol->equals($other->symbol)) {
      $replacements = [];
      foreach ($this->params as $index => $this_param) {
        $other_param = $other->params[$index];
        if ($this_param->binding && $other_param->binding) {
          if ($unification = $this_param->unify($other_param)) {
            $replacements[$this_param->symbol->get_id()] = $unification;
          } else {
            return null;
          }
        } else if ($other_param->binding) {
          $replacements[$this_param->symbol->get_id()] = $other_param;
        } else {
          $replacements[$this_param->symbol->get_id()] = $this_param;
        }
      }

      return $this->bind_parameters($replacements);
    }

    return null;
  }

  function to_variant_string(): string {
    $out = '';
    foreach ($this->variants as $name => $fields) {
      $out .= " | $name$fields\n";
    }
    return $out;
  }

  function __toString(): string {
    if (empty($this->params)) {
      return "$this->ref";
    } else {
      return "$this->ref" . '(' . implode(', ', $this->params) . ')';
    }
  }

  static function from_array(RefSymbol $symbol, Ref $ref, array $params, array $variants): self {
    $new_variants = [];
    foreach ($variants as $name => $fields) {
      if ($fields === null) {
        $new_variants[$name] = new UnitVariantFields();
      } else if (array_keys($fields) !== range(0, count($fields) - 1)) {
        $new_variants[$name] = new NamedVariantFields($fields);
      } else {
        $new_variants[$name] = new OrderedVariantFields($fields);
      }
    }
    return new self($symbol, $ref, $params, $new_variants);
  }

  static function matches(Type $other): bool {
    return $other->unwrap() instanceof self;
  }
}
