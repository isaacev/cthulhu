<?php

use \Cthulhu\Parser\AST;
use \Cthulhu\Parser\Lexer\TokenType;

require_once 'ast.php';

class ParserTest extends \PHPUnit\Framework\TestCase {
  private function expr(string $str, ?AST\Expression $expected) {
    $found = \Cthulhu\Parser\Parser::from_string($str)->parse_expr();
    $this->assertEquals($found, $expected);
    $this->assertEquals($found->jsonSerialize(), $expected->jsonSerialize());
  }

  private function stmt(string $str, ?AST\Statement $expected) {
    $found = \Cthulhu\Parser\Parser::from_string($str)->parse_stmt();
    $this->assertEquals($found, $expected);
    $this->assertEquals($found->jsonSerialize(), $expected->jsonSerialize());
  }

  private function prog(string $str, ?AST\Root $expected) {
    $found = \Cthulhu\Parser\Parser::from_string($str)->parse();
    $this->assertEquals($found, $expected);
    $this->assertEquals($found->jsonSerialize(), $expected->jsonSerialize());
  }

  public function test_str_literal_expression() {
    $this->expr('"hello world"', str('hello world'));
  }

  public function test_num_literal_expression() {
    $this->expr('123', num(123));
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
    $this->expr('a +', null);
  }

  public function test_expr_group_eof_error() {
    $this->expectExceptionMessage('unexpected end of file');
    $this->expr('(a + b', null);
  }

  public function test_expr_group_unexpected_token_error() {
    $this->expectExceptionMessage('unexpected ] at (1:7)');
    $this->expr('(a + b]', null);
  }

  public function test_if_expr() {
    $this->expr('if a { b; }',
      ifelse(ident('a'),
        block([
          exprStmt(ident('b'))
        ]),
        null
      )
    );
  }

  public function test_if_else_expr() {
    $this->expr('if a { b; } else { c; }',
      ifelse(ident('a'),
        block([
          exprStmt(ident('b'))
        ]),
        block([
          exprStmt(ident('c'))
        ])
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
        block([
          exprStmt(ident('c'))
        ])
      )
    );

    $this->expr('fn (a: Int, b: Int): Void { c; d; }',
      fn(
        [
          param('a', nameNote('Int')),
          param('b', nameNote('Int'))
        ],
        nameNote('Void'),
        block([
          exprStmt(ident('c')),
          exprStmt(ident('d'))
        ])
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
    $this->prog('let a = b; let c = d;', root(
      block([
        let('a', ident('b')),
        let('c', ident('d'))
      ])
    ));
  }
}
