<?php

use \Cthulhu\Types;
use \Cthulhu\Types\Binding;
use \Cthulhu\Types\Checker;

require_once 'ast.php';

class TypesTest extends \PHPUnit\Framework\TestCase {
  private function expr($expr, $expected, $binding) {
    $found = Checker::check_expr($expr, $binding);
    $this->assertEquals($expected, $found);
    $this->assertEquals($expected->jsonSerialize(), $found->jsonSerialize());
  }

  private function stmt($stmt, $expected, $binding) {
    $found = Checker::check_stmt($stmt, $binding);
    $table = $found ? $found->to_table() : [];
    $this->assertEquals($expected, $table);
  }

  private function stmts($stmts, $expected) {
    $binding = null;
    foreach ($stmts as $stmt) {
      $binding = Checker::check_stmt($stmt, $binding);
    }
    $table = $binding ? $binding->to_table() : [];
    $this->assertEquals($expected, $table);
  }

  public function test_builtin_type_relations() {
    // Builtin types do accept themselves
    $this->assertTrue((new Types\StrType())->accepts(new Types\StrType()));
    $this->assertTrue((new Types\NumType())->accepts(new Types\NumType()));
    $this->assertTrue((new Types\BoolType())->accepts(new Types\BoolType()));

    // Builtin types do not accept other types
    $this->assertFalse((new Types\StrType())->accepts(new Types\NumType()));
    $this->assertFalse((new Types\NumType())->accepts(new Types\StrType()));
    $this->assertFalse((new Types\BoolType())->accepts(new Types\StrType()));
  }

  public function test_number_literal_expression() {
    $this->expr(num(123), new Types\NumType(), null);
  }

  public function test_string_literal_expression() {
    $this->expr(str('hello'), new Types\StrType(), null);
  }

  public function test_identifier_expression() {
    $binding = new Binding(null, 'a', new Types\StrType());
    $this->expr(ident('a'), new Types\StrType(), $binding);
  }

  public function test_binary_expression() {
    $this->expr(binary('+', num(0), num(1)), new Types\NumType(), null);
    $this->expr(binary('-', num(0), num(1)), new Types\NumType(), null);
    $this->expr(binary('*', num(0), num(1)), new Types\NumType(), null);
    $this->expr(binary('/', num(0), num(1)), new Types\NumType(), null);
    $this->expr(binary('>', num(0), num(1)), new Types\BoolType(), null);
    $this->expr(binary('>=', num(0), num(1)), new Types\BoolType(), null);
    $this->expr(binary('<', num(0), num(1)), new Types\BoolType(), null);
    $this->expr(binary('<=', num(0), num(1)), new Types\BoolType(), null);
  }

  public function test_bad_numeric_binary_expr_left_side() {
    $this->expectException(Types\Errors\TypeMismatch::class);
    $this->expr(binary('+', str('abc'), num(0)), new Types\NumType(), null);
  }

  public function test_bad_numeric_binary_expr_right_side() {
    $this->expectException(Types\Errors\TypeMismatch::class);
    $this->expr(binary('+', num(0), str('abc')), new Types\NumType(), null);
  }

  public function test_bad_relation_binary_expr_left_side() {
    $this->expectException(Types\Errors\TypeMismatch::class);
    $this->expr(binary('>', str('abc'), num(0)), new Types\BoolType(), null);
  }

  public function test_bad_relation_binary_expr_right_side() {
    $this->expectException(Types\Errors\TypeMismatch::class);
    $this->expr(binary('>', num(0), str('abc')), new Types\BoolType(), null);
  }

  public function test_undeclared_identifier_expression() {
    $binding = new Binding(null, 'b', new Types\StrType());
    $this->expectException(Types\Errors\UndeclaredVariable::class);
    $this->expr(ident('a'), new Types\StrType(), $binding);
  }

  public function test_let_stmt() {
    $this->stmt(
      let('a', num(123)),
      [ 'a' => new Types\NumType() ],
      null
    );
  }

  public function test_expr_stmt() {
    $this->stmt(exprStmt(num(123)), [ ], null);
  }

  public function test_redeclared_variable() {
    $binding = new Binding(null, 'a', new Types\StrType());
    $this->expr(ident('a'), new Types\StrType(), $binding);
    $this->stmt(let('a', num(123)), [ 'a' => new Types\NumType() ], $binding);
  }

  public function test_multistep_binding_resolution() {
    $binding0 = new Binding(null, 'a', new Types\StrType());
    $binding1 = new Binding($binding0, 'b', new Types\NumType());
    $binding2 = new Binding($binding1, 'c', new Types\NumType());
    $this->expr(ident('a'), new Types\StrType(), $binding2);
  }

  public function test_statement_bindings() {
    $this->stmts([
      let('a', num(1)),
      let('b', num(0)),
      let('c', ident('b')),
      let('b', str('hello')),
      let('d', ident('b')),
    ], [
      'a' => new Types\NumType(),
      'b' => new Types\StrType(),
      'c' => new Types\NumType(),
      'd' => new Types\StrType()
    ]);
  }
}
