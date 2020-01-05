<?php

namespace Cthulhu\Parser\Lexer;

abstract class TokenType {
  const ERROR           = 'ERROR';
  const EOF             = 'EOF';
  const COMMENT         = 'Comment';
  const LITERAL_INT     = 'Int';
  const LITERAL_FLOAT   = 'Float';
  const LITERAL_STR     = 'Str';
  const LITERAL_BOOL    = 'Bool';
  const UPPER_NAME      = 'Upper-case Name';
  const LOWER_NAME      = 'Lower-case Name';
  const TYPE_PARAM      = 'Type Parameter';
  const KEYWORD_LET     = 'Let';
  const KEYWORD_IF      = 'If';
  const KEYWORD_ELSE    = 'Else';
  const KEYWORD_FN      = 'Fn';
  const KEYWORD_USE     = 'Use';
  const KEYWORD_MOD     = 'Mod';
  const KEYWORD_NATIVE  = 'Native';
  const KEYWORD_TYPE    = 'Type';
  const KEYWORD_MATCH   = 'Match';
  const UNDERSCORE      = '_';
  const CARET           = '^';
  const PIPE            = '|';
  const TRIANGLE        = '|>';
  const POUND           = '#';
  const BRACE_LEFT      = '{';
  const BRACE_RIGHT     = '}';
  const BRACKET_LEFT    = '[';
  const BRACKET_RIGHT   = ']';
  const PAREN_LEFT      = '(';
  const PAREN_RIGHT     = ')';
  const PLUS            = '+';
  const PLUS_PLUS       = '++';
  const DASH            = '-';
  const THIN_ARROW      = '->';
  const FAT_ARROW       = '=>';
  const DOUBLE_EQUALS   = '==';
  const STAR            = '*';
  const SLASH           = '/';
  const SEMICOLON       = ';';
  const EQUALS          = '=';
  const COLON           = ':';
  const DOUBLE_COLON    = '::';
  const COMMA           = ',';
  const DOT             = '.';
  const LESS_THAN       = '<';
  const LESS_THAN_EQ    = '<=';
  const GREATER_THAN    = '>';
  const GREATER_THAN_EQ = '>=';
}
