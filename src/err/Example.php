<?php

namespace Cthulhu\err;

use Cthulhu\lib\fmt\Formatter;
use Cthulhu\loc\Directory;
use Cthulhu\loc\File;
use Cthulhu\loc\Filepath;

class Example implements Reportable {
  public string $example;

  public function __construct(string $example) {
    $this->example = $example;
  }

  public function print(Formatter $f): Formatter {
    $file       = new File(new Filepath(new Directory('', false), '<example>', 'cth'), $this->example);
    $all_tokens = Snippet::all_tokens($file);

    $f->increment_tab_stop(2)
      ->newline_if_not_already()
      ->tab();

    $prev_line = 1;
    $prev_col  = 1;
    foreach ($all_tokens as $token) {
      $total_newlines = $token->span->from->line - $prev_line;
      if ($total_newlines > 0) {
        $prev_col  = 1;
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
        ->print($token->lexeme)
        ->reset_styles_if($has_styles);
    }

    return $f->pop_tab_stop();
  }
}
