<?php

use \Cthulhu\Parser\AST;
use \Cthulhu\Parser\Lexer\TokenType;

function parse_expr(string $str) {
  return \Cthulhu\Parser\Parser::from_string($str)->parse_expr();
}

function parse_stmt(string $str) {
  return \Cthulhu\Parser\Parser::from_string($str)->parse();
}

function ident($name) {
  return [
    'type' => 'Identifier',
    'name' => $name
  ];
}

function binary($op, $left, $right) {
  return [
    'type' => 'BinaryOperator',
    'operator' => $op,
    'left' => $left,
    'right' => $right
  ];
}

function ifelse($cond, $if_clause, $else_clause) {
  return [
    'type' => 'IfExpression',
    'condition' => $cond,
    'if_clause' => $if_clause,
    'else_clause' => $else_clause
  ];
}

function exprStmt($expr) {
  return [
    'type' => 'ExpressionStatement',
    'expression' => $expr
  ];
}

function nameNote($name) {
  return [
    'type' => 'NameAnnotation',
    'name' => $name
  ];
}

function param($name, $note) {
  return [
    'name' => $name,
    'annotation' => $note
  ];
}

function fn($params, $ret, $body) {
  return [
    'type' => 'FnExpression',
    'parameters' => $params,
    'return_annotation' => $ret,
    'body' => $body
  ];
}

function call($callee, $args) {
  return [
    'type' => 'CallExpression',
    'callee' => $callee,
    'arguments' => $args
  ];
}

function let($name, $expr) {
  return [
    'type' => 'LetStatement',
    'name' => $name,
    'expression' => $expr
  ];
}

function root($stmts) {
  return [
    'type' => 'Root',
    'statements' => $stmts
  ];
}

class ParserTest extends \PHPUnit\Framework\TestCase {
  private function expr(string $str, $json) {
    $ast = \Cthulhu\Parser\Parser::from_string($str)->parse_expr();
    $this->assertEquals($ast->jsonSerialize(), $json);
  }

  private function stmt(string $str, $json) {
    $ast = \Cthulhu\Parser\Parser::from_string($str)->parse_stmt();
    $this->assertEquals($ast->jsonSerialize(), $json);
  }

  private function prog(string $str, $json) {
    $ast = \Cthulhu\Parser\Parser::from_string($str)->parse();
    $this->assertEquals($ast->jsonSerialize(), $json);
  }

  public function test_identifier_expression() {
    $this->expr('a', ident('a'));
    $this->expr('abc', ident('abc'));
  }

  public function test_binary_expression() {
    $this->expr('a + b', binary('+', ident('a'), ident('b')));
    $this->expr('a - b', binary('-', ident('a'), ident('b')));
    $this->expr('a * b', binary('*', ident('a'), ident('b')));
    $this->expr('a / b', binary('/', ident('a'), ident('b')));
  }

  public function test_binary_expression_precedence() {
    $this->expr('a + b * c',
      binary('+',
        ident('a'),
        binary('*',
          ident('b'),
          ident('c')
        )
      )
    );

    $this->expr('(a + b) * c',
      binary('*',
        binary('+',
          ident('a'),
          ident('b')
        ),
        ident('c')
      )
    );
  }

  public function test_binary_expression_eof_error() {
    $this->expectExceptionMessage('unexpected end of file');
    parse_expr('a +');
  }

  public function test_expr_group_eof_error() {
    $this->expectExceptionMessage('unexpected end of file');
    parse_expr('(a + b');
  }

  public function test_expr_group_unexpected_token_error() {
    $this->expectExceptionMessage('unexpected ] at (1:7)');
    parse_expr('(a + b]');
  }

  public function test_if_expr() {
    $this->expr('if a { b; }',
      ifelse(ident('a'),
        [
          exprStmt(ident('b'))
        ],
        null
      )
    );
  }

  public function test_if_else_expr() {
    $this->expr('if a { b; } else { c; }',
      ifelse(ident('a'),
        [
          exprStmt(ident('b'))
        ],
        [
          exprStmt(ident('c'))
        ]
      )
    );
  }

  public function test_fn_expr() {
    $this->expr('fn (a: Int, b: Int): Void { c; }',
      fn(
        [
          param('a', nameNote('Int')),
          param('b', nameNote('Int'))
        ],
        nameNote('Void'),
        [
          exprStmt(ident('c'))
        ]
      )
    );

    $this->expr('fn (a: Int, b: Int): Void { c; d; }',
      fn(
        [
          param('a', nameNote('Int')),
          param('b', nameNote('Int'))
        ],
        nameNote('Void'),
        [
          exprStmt(ident('c')),
          exprStmt(ident('d'))
        ]
      )
    );
  }

  public function test_fn_expr_arg_error() {
    $this->expectExceptionMessage('unexpected end of file, wanted )');
    $this->expr('fn (', null);
  }

  public function test_fn_expr_annotation_eof_error() {
    $this->expectExceptionMessage('unexpected end of file');
    $this->expr('fn (a:', null);
  }

  public function test_fn_expr_annotation_token_error() {
    $this->expectExceptionMessage('unexpected { at (1:7)');
    $this->expr('fn (a:{', null);
  }

  public function test_fn_expr_body_error() {
    $this->expectExceptionMessage('unexpected end of file, wanted }');
    $this->expr('fn (): a {', null);
  }

  public function test_call_expr() {
    $this->expr('abc(d, e)',
      call(
        ident('abc'),
        [
          ident('d'),
          ident('e'),
        ]
      )
    );

    $this->expr('a * bc(d + e, f)',
      binary('*',
        ident('a'),
        call(
          ident('bc'),
          [
            binary('+', ident('d'), ident('e')),
            ident('f')
          ]
        )
      )
    );

    $this->expr('(a * bc)(d + e, f)',
      call(
        binary('*', ident('a'), ident('bc')),
        [
          binary('+', ident('d'), ident('e')),
          ident('f')
        ]
      )
    );
  }

  public function test_call_expr_error() {
    $this->expectExceptionMessage('unexpected end of file, wanted )');
    $this->expr('abc(', null);
  }

  public function test_prefix_expr_error() {
    $this->expectExceptionMessage('unexpected ) at (1:1)');
    $this->expr(')', null);
  }

  public function test_let_stmt() {
    $this->stmt('let a = b;',
      let('a', ident('b'))
    );
  }

  public function test_parse() {
    $this->prog('let a = b; let c = d;', root([
      let('a', ident('b')),
      let('c', ident('d'))
    ]));
  }
}
