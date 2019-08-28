<?php

namespace Cthulhu\Kernel;

use Cthulhu\Codegen\Builder;
use Cthulhu\IR;
use Cthulhu\Types;

class Kernel {
  public static function IO(): IR\NativeModule {
    return self::module('IO', [
      self::fn([
        'name' => 'println',
        'signature' => new Types\FnType([ new Types\StrType() ], new Types\VoidType()),
        'builder' => (new Builder)
          ->keyword('function')
          ->space()
          ->identifier('println')
          ->paren_left()
          ->variable('str')
          ->paren_right()
          ->brace_left()
          ->increase_indentation()
          ->newline_then_indent()
          ->keyword('echo')
          ->space()
          ->variable('str')
          ->dot()
          ->string_literal('\n')
          ->semicolon()
          ->decrease_indentation()
          ->newline_then_indent()
          ->brace_right()
      ])
    ]);
  }

  private static function module(string $name, array $items): IR\NativeModule {
    $module = new IR\NativeModule($name);
    foreach ($items as $item) {
      switch ($item['type']) {
        case 'fn':
          $name = $item['fields']['name'];
          $signature = $item['fields']['signature'];
          $builder = $item['fields']['builder'];
          $module->fn($name, $signature, $builder);
          break;
      }
    }
    return $module;
  }

  private static function fn(array $fields) {
    return [
      'type' => 'fn',
      'fields' => $fields
    ];
  }
}
