<?php

namespace Cthulhu\ir\types;

use Cthulhu\loc\Spanlike;

abstract class Type implements Walkable {
  abstract public function equals(Type $other): bool;

  /**
   * @param string $op
   * @param Type   ...$operands
   * @return Type|null
   * @noinspection PhpUnusedParameterInspection
   */
  public function apply_operator(string $op, Type ...$operands): ?Type {
    return null;
  }

  abstract public function __toString(): string;

  /**
   * @param Type     $a
   * @param Type     $b
   * @param Spanlike $spanlike
   * @param Type[]   $replacements
   * @return Type[]
   */
  public static function infer_free_types(Type $a, Type $b, Spanlike $spanlike, array &$replacements = []) {
    $a->compare($b, function (Walkable $c, Walkable $d) use (&$replacements, $spanlike): void {
      if ($c instanceof FreeType && $d instanceof Type) {
        $free_id = $c->symbol->get_id();
        if ($solution = @$replacements[$free_id]) {
          if ($solution->equals($d) === false) {
            throw Errors::unsolvable_type_parameter($spanlike, $c->name, $solution, $d);
          }
        } else {
          $replacements[$free_id] = $d;
        }
      }
    });
    return $replacements;
  }

  /**
   * @param Type   $t
   * @param Type[] $replacements
   * @return Type
   * @noinspection PhpIncompatibleReturnTypeInspection
   */
  public static function replace_free_types(Type $t, array $replacements): Type {
    return $t->transform(function (Walkable $u) use (&$replacements): ?Type {
        if ($u instanceof FreeType) {
          $free_id = $u->symbol->get_id();
          if ($replacement = @$replacements[$free_id]) {
            return $replacement;
          }
        }
        return null;
      }) ?? $t;
  }

  /** @noinspection PhpIncompatibleReturnTypeInspection */
  public static function replace_free_types_with_unknown(Type $t): Type {
    return $t->transform(function (Walkable $u): ?Type {
        if ($u instanceof FreeType) {
          return new UnknownType();
        }
        return null;
      }) ?? $t;
  }

  /**
   * @param Type $t
   * @return Type
   * @noinspection PhpIncompatibleReturnTypeInspection
   */
  public static function freeze_free_types(Type $t): Type {
    return $t->transform(function (Walkable $u): ?Type {
        if ($u instanceof FreeType) {
          return new FixedType($u->symbol, $u->name);
        }
        return null;
      }) ?? $t;
  }

  public static function has_unknowns(Type $t): bool {
    $has_unknowns = false;
    $t->walk(function (Walkable $w) use (&$has_unknowns) {
      if ($w instanceof UnknownType) {
        $has_unknowns = true;
      }
    });
    return $has_unknowns;
  }

  /**
   * @param Type $a
   * @param Type $b
   * @return Type
   * @noinspection PhpIncompatibleReturnTypeInspection
   */
  public static function replace_unknowns(Type $a, Type $b): Type {
    return $a->compare_and_transform($b, function (Walkable $c, Walkable $d): ?Type {
        if ($c instanceof UnknownType) {
          return $d;
        } else if ($d instanceof UnknownType) {
          return $c;
        }
        return null;
      }) ?? $a;
  }
}
