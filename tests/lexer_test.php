<?php

use \Cthulhu\Parser\Lexer\Lexer;
use \Cthulhu\Parser\Lexer\TokenType;
use \Cthulhu\Parser\Lexer\Point;

/**
 * @group lexer
 */
class LexerTest extends \PHPUnit\Framework\TestCase {
  private function tok(string $str, string $type, ?string $lexeme = null) {
    $tok = Lexer::from_string($str)->next();
    $this->assertEquals($type, $tok->type);
    if ($lexeme !== null) {
      $this->assertEquals($lexeme, $tok->lexeme);
    }
  }

  public function test_tokens() {
    $this->tok('123',  TokenType::LITERAL_NUM, '123');
    $this->tok('"a "', TokenType::LITERAL_STR, '"a "');
    $this->tok('abc',  TokenType::IDENT, 'abc');
    $this->tok('let',  TokenType::KEYWORD_LET);
    $this->tok('if',   TokenType::KEYWORD_IF);
    $this->tok('else', TokenType::KEYWORD_ELSE);
    $this->tok('fn',   TokenType::KEYWORD_FN);
    $this->tok('{',    TokenType::BRACE_LEFT);
    $this->tok('}',    TokenType::BRACE_RIGHT);
    $this->tok('[',    TokenType::BRACKET_LEFT);
    $this->tok(']',    TokenType::BRACKET_RIGHT);
    $this->tok('(',    TokenType::PAREN_LEFT);
    $this->tok(')',    TokenType::PAREN_RIGHT);
    $this->tok('+',    TokenType::PLUS);
    $this->tok('-',    TokenType::DASH);
    $this->tok('*',    TokenType::STAR);
    $this->tok('/',    TokenType::SLASH);
    $this->tok(';',    TokenType::SEMICOLON);
    $this->tok('=',    TokenType::EQUALS);
    $this->tok(':',    TokenType::COLON);
    $this->tok('::',   TokenType::DOUBLE_COLON);
    $this->tok(',',    TokenType::COMMA);
    $this->tok('.',    TokenType::DOT);
    $this->tok('<',    TokenType::LESS_THAN);
    $this->tok('<=',   TokenType::LESS_THAN_EQ);
    $this->tok('>',    TokenType::GREATER_THAN);
    $this->tok('>=',   TokenType::GREATER_THAN_EQ);
  }

  public function test_unknown_character() {
    $this->expectExceptionMessage('unknown character');
    Lexer::from_string('#')->next();
  }

  public function test_string_unclosed_by_newline() {
    $this->expectExceptionMessage('unclosed string');
    Lexer::from_string("\"hello\nworld\"")->next();
  }

  public function test_string_unclosed_by_eof() {
    $this->expectExceptionMessage('unclosed string');
    Lexer::from_string('"hello')->next();
  }

  public function test_whitespace_handling()  {
    $lex = Lexer::from_string(" hello\n\tworld");
    $tok1 = $lex->next();
    $tok2 = $lex->next();
    $tok3 = $lex->next();
    $this->assertEquals(new Point(1, 2, 1), $tok1->span->from);
    $this->assertEquals(new Point(2, 2, 8), $tok2->span->from);
    $this->assertEquals(null, $tok3);
  }
}
