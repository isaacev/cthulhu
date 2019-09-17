<?php

namespace Cthulhu\Kernel;

use Cthulhu\Codegen\Builder;
use Cthulhu\Codegen\PHP;
use Cthulhu\IR;
use Cthulhu\Types;

class Kernel {
  public static function IO(): IR\NativeModule {
    return self::module('IO', [
      self::fn([
        'name' => 'println',
        'signature' => new Types\FnType([ new Types\StrType() ], new Types\VoidType()),
        'stmt' => function (IR\Symbol $symbol) {
          $ref = new PHP\Reference([$symbol->name]);
          $str = new PHP\Variable('str');
          $params = [ $str ];
          $body = new PHP\BlockNode([
            new PHP\EchoStmt(
              new PHP\BinaryExpr('.',
                new PHP\VariableExpr($str),
                new PHP\StrExpr('\\n'))),
          ]);
          return new PHP\FuncStmt($ref, $params, $body);
        },
      ])
    ]);
  }

  private static function module(string $name, array $items): IR\NativeModule {
    $module = new IR\NativeModule($name);
    foreach ($items as $item) {
      switch ($item['type']) {
        case 'fn':
          $name = $item['fields']['name'];
          $symbol = new IR\Symbol($name, null, $module->scope->symbol);
          $signature = $item['fields']['signature'];
          $stmt = $item['fields']['stmt']($symbol);
          $module->fn($symbol, $signature, $stmt);
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
