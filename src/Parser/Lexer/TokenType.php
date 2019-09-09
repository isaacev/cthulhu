<?php

namespace Cthulhu\Parser\Lexer;

abstract class TokenType {
  const ERROR               = 'ERROR';
  const EOF                 = 'EOF';
  const LITERAL_NUM         = 'Num';
  const LITERAL_STR         = 'Str';
  const LITERAL_BOOL        = 'Bool';
  const IDENT               = 'Ident';
  const KEYWORD_LET         = 'Let';
  const KEYWORD_IF          = 'If';
  const KEYWORD_ELSE        = 'Else';
  const KEYWORD_FN          = 'Fn';
  const KEYWORD_USE         = 'Use';
  const KEYWORD_MOD         = 'Mod';
  const BRACE_LEFT          = '{';
  const BRACE_RIGHT         = '}';
  const BRACKET_LEFT        = '[';
  const BRACKET_RIGHT       = ']';
  const PAREN_LEFT          = '(';
  const PAREN_RIGHT         = ')';
  const PLUS                = '+';
  const DASH                = '-';
  const THIN_ARROW          = '->';
  const STAR                = '*';
  const SLASH               = '/';
  const SEMICOLON           = ';';
  const EQUALS              = '=';
  const COLON               = ':';
  const DOUBLE_COLON        = '::';
  const COMMA               = ',';
  const DOT                 = '.';
  const LESS_THAN           = '<';
  const LESS_THAN_EQ        = '<=';
  const GREATER_THAN        = '>';
  const GREATER_THAN_EQ     = '>=';
}
