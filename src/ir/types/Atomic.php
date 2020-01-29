<?php

namespace Cthulhu\ir\types;

class Atomic extends ConcreteType {
  private string $stringify;

  public function __construct(string $name, ?string $stringify = null) {
    parent::__construct($name);
    $this->stringify = $stringify ?? $name;
  }

  public function fresh(ParameterContext $ctx): Atomic {
    return $this;
  }

  public function __toString(): string {
    return $this->stringify;
  }

  public static function str(): Atomic {
    return new Atomic('Str');
  }

  public static function int(): Atomic {
    return new Atomic('Int');
  }

  public static function float(): Atomic {
    return new Atomic('Float');
  }

  public static function bool(): Atomic {
    return new Atomic('Bool');
  }

  public static function unit(): Atomic {
    return new Atomic('Unit', '()');
  }

  public static function is_unit(Type $type): bool {
    return (
      $type instanceof Atomic &&
      $type->name === 'Unit'
    );
  }
}
