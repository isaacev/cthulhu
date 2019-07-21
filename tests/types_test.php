<?php

use \Cthulhu\Types;
use \Cthulhu\Types\Checker;

require_once 'ast.php';

class TypesTest extends \PHPUnit\Framework\TestCase {
  private function expr($expr, $expected) {
    $scope = new Types\Scope(null);
    $found = Checker::check_expr($scope, $expr);
    $this->assertEquals($expected, $found);
    $this->assertEquals($expected->jsonSerialize(), $found->jsonSerialize());
  }

  public function test_number_literal_expression() {
    $this->expr(num(123), new Types\NumType());
  }

  public function test_string_literal_expression() {
    $this->expr(str('hello'), new Types\StrType());
  }
}
