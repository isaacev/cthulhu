<?php

use \Cthulhu\Types;
use \Cthulhu\Types\Scope;
use \Cthulhu\Types\Checker;

require_once 'ast.php';

class TypesTest extends \PHPUnit\Framework\TestCase {
  private function expr($expr, $expected, $scope = null) {
    $scope = ($scope === null) ? new Scope(null) : $scope;
    $found = Checker::check_expr($scope, $expr);
    $this->assertEquals($expected, $found);
    $this->assertEquals($expected->jsonSerialize(), $found->jsonSerialize());
  }

  public function test_builtin_type_relations() {
    // Builtin types do accept themselves
    $this->assertTrue((new Types\StrType())->accepts(new Types\StrType()));
    $this->assertTrue((new Types\NumType())->accepts(new Types\NumType()));

    // Builtin types do not accept other types
    $this->assertFalse((new Types\StrType())->accepts(new Types\NumType()));
    $this->assertFalse((new Types\NumType())->accepts(new Types\StrType()));
  }

  public function test_number_literal_expression() {
    $this->expr(num(123), new Types\NumType());
  }

  public function test_string_literal_expression() {
    $this->expr(str('hello'), new Types\StrType());
  }

  public function test_identifier_expression() {
    $scope = new Scope(null);
    $scope->set_local_variable('a', new Types\StrType());
    $this->expr(ident('a'), new Types\StrType(), $scope);
  }

  public function test_undeclared_identifier_expression() {
    $this->expectException(Types\Errors\UndeclaredVariable::class);
    $this->expr(ident('a'), new Types\StrType());
  }
}
