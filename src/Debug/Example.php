<?php

namespace Cthulhu\Debug;

use Cthulhu\Parser\Lexer\Lexer;
use Cthulhu\Source;

class Example implements Reportable {
  public $example;

  function __construct(string $example) {
    $this->example = $example;
  }

  public function print(Teletype $tty): Teletype {
    $file = new Source\File('<example>', $this->example);
    $all_tokens = Lexer::to_tokens($file, Lexer::MODE_RELAXED);

    $tty->increase_tab_stop(2);

    $tty
      ->newline_if_not_empty()
      ->tab();

    $prev_line = 1;
    $prev_col = 1;
    foreach ($all_tokens as $token) {
      $total_newlines = $token->span->from->line - $prev_line;
      if ($total_newlines > 0) {
        $prev_col = 1;
        $prev_line = $token->span->to->line;
        $tty
          ->repeat(PHP_EOL, $total_newlines)
          ->tab();
      }

      $total_spaces = $token->span->from->column - $prev_col;
      $prev_col     = $token->span->to->column;
      $styles       = Snippet::token_styles($token);
      $has_styles   = !empty($styles);

      $tty
        ->spaces($total_spaces)
        ->apply_styles_if($has_styles, ...$styles)
        ->printf($token->lexeme)
        ->reset_styles_if($has_styles);
    }

    $tty->pop_tab_stop();

    return $tty;
  }
}
