<?php

namespace Cthulhu\ir\patterns;

use Cthulhu\ast\nodes as ast;
use Cthulhu\ir\types;

abstract class Pattern {
  abstract public function __toString(): string;

  public static function from(ast\Pattern $pattern, types\Type $type): self {
    if ($pattern instanceof ast\FormPattern) {
      assert($type instanceof types\Enum);
      $form_name = $pattern->path->tail->value;
      assert(is_string($form_name));
      $form_type = $type->forms[$form_name];

      if ($form_type instanceof types\Record && $pattern instanceof ast\NamedFormPattern) {
        $mapping = [];
        foreach ($pattern->pairs as $pair) {
          $field_name           = $pair->name->value;
          $field_pattern        = $pair->pattern;
          $field_type           = $form_type->fields[$field_name];
          $mapping[$field_name] = self::from($field_pattern, $field_type);
        }

        $fields = new NamedFormFields($mapping);
        return new FormPattern($form_name, $fields);
      } else if ($form_type instanceof types\Tuple && $pattern instanceof ast\OrderedFormPattern) {
        $order = [];
        foreach ($form_type->members as $index => $field_type) {
          $field_pattern = $pattern->order[$index];
          $order[]       = self::from($field_pattern, $field_type);
        }
        $fields = new OrderedFormFields($order);
        return new FormPattern($form_name, $fields);
      } else {
        return new FormPattern($form_name, null);
      }
    } else if ($pattern instanceof ast\ConstPattern) {
      if ($pattern->literal instanceof ast\StrLiteral) {
        return new StrPattern($pattern->literal->str_value);
      } else if ($pattern->literal instanceof ast\FloatLiteral) {
        return new FloatPattern($pattern->literal->float_value);
      } else if ($pattern->literal instanceof ast\IntLiteral) {
        return new IntPattern($pattern->literal->int_value);
      } else if ($pattern->literal instanceof ast\BoolLiteral) {
        return new BoolPattern($pattern->literal->bool_value);
      } else {
        die('unreachable at ' . __LINE__ . ' in ' . __FILE__ . PHP_EOL);
      }
    } else {
      return new WildcardPattern();
    }
  }
}
