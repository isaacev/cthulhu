<?php

namespace Cthulhu\Debug;

use Cthulhu\Parser\Lexer\Lexer;
use Cthulhu\Source;
use Cthulhu\utils\fmt\Formatter;

class Example implements Reportable {
  public $example;

  function __construct(string $example) {
    $this->example = $example;
  }

  public function print(Formatter $f): Formatter {
    $file = new Source\File('<example>', $this->example);
    $all_tokens = Lexer::to_tokens($file, Lexer::MODE_RELAXED);

    $f->increment_tab_stop(2)
      ->newline_if_not_already()
      ->tab();

    $prev_line = 1;
    $prev_col = 1;
    foreach ($all_tokens as $token) {
      $total_newlines = $token->span->from->line - $prev_line;
      if ($total_newlines > 0) {
        $prev_col = 1;
        $prev_line = $token->span->to->line;
        $f->repeat(PHP_EOL, $total_newlines)
          ->tab();
      }

      $total_spaces = $token->span->from->column - $prev_col;
      $prev_col     = $token->span->to->column;
      $styles       = Snippet::token_styles($token);
      $has_styles   = !empty($styles);

      $f->spaces($total_spaces)
        ->apply_styles_if($has_styles, ...$styles)
        ->printf($token->lexeme)
        ->reset_styles_if($has_styles);
    }

    return $f->pop_tab_stop();
  }
}
