<?php

namespace Cthulhu\Analysis;

use Cthulhu\IR;
use Cthulhu\Parser;
use Cthulhu\Source;

class Linker extends IR\Scope {
  public $includes = [];
  protected $trace = [];

  function __construct() {
    $this->add_binding(IR\Binding::for_module(self::kernel()));
  }

  public function resolve_library(string $filepath): IR\Binding {
    if (in_array($filepath, $this->trace)) {
      throw new \Exception('dependency cycle');
    } else {
      $this->trace[] = $filepath;
    }

    $file = new Source\File($filepath, file_get_contents($filepath));
    $ast = Parser\Parser::file_to_ast($file);
    $module = Analyzer::ast_to_module($this, $ast);
    $this->includes[] = $module;
    $binding = IR\Binding::for_module($module->scope);
    $this->add_binding($binding);
    array_pop($this->trace);
    return $binding;
  }

  public function resolve_name(string $name): ?IR\Binding {
    if ($binding = parent::resolve_name($name)) {
      return $binding;
    } else if ($filepath = self::name_in_stdlib($name)) {
      return $this->resolve_library($filepath);
    } else {
      return null;
    }
  }

  protected const STDLIB_DIR = __DIR__ . '/../stdlib';

  protected static function name_in_stdlib(string $name) {
    return realpath(self::STDLIB_DIR . '/' . $name . '.cth');
  }

  protected static function kernel(): IR\ModuleScope {
    $scope = new IR\ModuleScope(null, 'kernel');

    $int_symbol = new IR\Symbol('Num', null, $scope->symbol);
    $int_type = new IR\Types\IntType();
    $scope->add_binding(IR\Binding::for_type($int_symbol, $int_type));
    $int_type->add_binop('+', $int_type, $int_type);

    $str_symbol = new IR\Symbol('Str', null, $scope->symbol);
    $str_type = new IR\Types\StrType();
    $scope->add_binding(IR\Binding::for_type($str_symbol, $str_type));

    return $scope;
  }
}
