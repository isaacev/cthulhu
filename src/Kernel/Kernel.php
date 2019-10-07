<?php

namespace Cthulhu\Kernel;

use Cthulhu\Codegen\Builder;
use Cthulhu\Codegen\PHP;
use Cthulhu\Codegen\Renamer;
use Cthulhu\IR;

class Kernel {
  public static function Types(): IR\NativeModule {
    return self::module('Types', [
      self::type('Num', null),
      self::type('Str', null),
      self::type('Bool', null),
    ]);
  }

  public static function IO(\Cthulhu\Analysis\Context $ctx): IR\NativeModule {
    return self::module('IO', [
      self::fn([
        'name' => 'println',
        'signature' => new IR\Types\FunctionType([
          $ctx->raw_path_to_type('Types', 'Str')
        ], new IR\Types\UnitType()),
        'stmt' => function (Renamer $renamer, IR\Symbol $symbol) {
          $ref = new PHP\Reference($symbol, [ $renamer->resolve($symbol) ]);
          $str = $renamer->allocate_variable('str');
          $params = [ $str ];
          $body = new PHP\BlockNode([
            new PHP\EchoStmt(
              new PHP\BinaryExpr('.',
                new PHP\VariableExpr($str),
                new PHP\StrExpr('\\n'))),
          ]);
          return new PHP\FuncStmt($ref, $params, $body, ['inline' => true]);
        },
      ])
    ]);
  }

  public static function Random(\Cthulhu\Analysis\Context $ctx): IR\NativeModule {
    return self::module('Random', [
      self::fn([
        'name' => 'int',
        'signature' => new IR\Types\FunctionType([
          $ctx->raw_path_to_type('Types', 'Num'),
          $ctx->raw_path_to_type('Types', 'Num')
        ], $ctx->raw_path_to_type('Types', 'Num')),
        'stmt' => function (Renamer $renamer, IR\Symbol $symbol) {
          $ref = new PHP\Reference($symbol, [ $renamer->resolve($symbol) ]);
          $a = $renamer->allocate_variable('a');
          $b = $renamer->allocate_variable('b');
          $params = [ $a, $b ];
          $body = new PHP\BlockNode([
            new PHP\ReturnStmt(
              new PHP\CallExpr(
                new PHP\ReferenceExpr($renamer->get_php_global('mt_rand')),
                [
                  new PHP\VariableExpr($a),
                  new PHP\VariableExpr($b),
                ]
              )
            )
          ]);
          return new PHP\FuncStmt($ref, $params, $body, ['inline' => true]);
        }
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
          $stmt = $item['fields']['stmt'];
          $module->fn($symbol, $signature, $stmt);
          break;
        case 'type':
          $name = $item['name'];
          $symbol = new IR\Symbol($name, null, $module->scope->symbol);
          $module->type($symbol, $item['hidden']);
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

  private static function type(string $name, ?IR\Types\Type $hidden) {
    return [
      'type'   => 'type',
      'name'   => $name,
      'hidden' => $hidden,
    ];
  }
}
