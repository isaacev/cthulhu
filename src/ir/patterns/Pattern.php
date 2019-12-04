<?php

namespace Cthulhu\ir\patterns;

use Cthulhu\ir\nodes;
use Cthulhu\ir\types;

abstract class Pattern {
  abstract function __toString(): string;

  static function from(nodes\Pattern $pattern, types\Type $type): self {
    $type = $type->unwrap();
    if ($pattern instanceof nodes\VariantPattern) {
      assert($type instanceof types\UnionType);
      $form = $type->variants[$pattern->ref->tail_segment->value];
      if ($form instanceof types\NamedVariantFields) {
        assert($pattern->fields instanceof nodes\NamedVariantPatternFields);
        $mapping = [];
        foreach ($form->mapping as $name => $sub_type) {
          $sub_pattern    = $pattern->fields->mapping[$name]->pattern;
          $mapping[$name] = self::from($sub_pattern, $sub_type);
        }
        $fields = new NamedVariantFields($mapping);
        return new VariantPattern($pattern->ref->tail_segment->value, $fields);
      } else if ($form instanceof types\OrderedVariantFields) {
        assert($pattern->fields instanceof nodes\OrderedVariantPatternFields);
        $order = [];
        foreach ($form->order as $index => $sub_type) {
          $sub_pattern = $pattern->fields->order[$index]->pattern;
          $order[]     = self::from($sub_pattern, $sub_type);
        }
        $fields = new OrderedVariantFields($order);
        return new VariantPattern($pattern->ref->tail_segment->value, $fields);
      } else {
        return new VariantPattern($pattern->ref->tail_segment->value, null);
      }
    } else if ($pattern instanceof nodes\StrConstPattern) {
      return new StrPattern($pattern->value);
    } else if ($pattern instanceof nodes\FloatConstPattern) {
      return new FloatPattern($pattern->value);
    } else if ($pattern instanceof nodes\IntConstPattern) {
      return new IntPattern($pattern->value);
    } else if ($pattern instanceof nodes\BoolConstPattern) {
      return new BoolPattern($pattern->value);
    } else {
      return new WildcardPattern();
    }
  }
}