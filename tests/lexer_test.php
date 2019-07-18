<?php

use \Cthulhu\Parser\Lexer\Lexer;
use \Cthulhu\Parser\Lexer\TokenType;

class LexerTest extends \PHPUnit\Framework\TestCase {
  private function tok(string $str, string $type, ?string $lexeme = null) {
    $tok = Lexer::from_string($str)->next();
    $this->assertEquals($tok->type, $type);
    if ($lexeme !== null) {
      $this->assertEquals($tok->lexeme, $lexeme);
    }
  }

  public function test_tokens() {
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
  }

  public function test_unknown_character() {
    $this->expectExceptionMessage('unknown character');
    Lexer::from_string('#')->next();
  }
}
