<?php

namespace Cthulhu\Debug;

class Paragraph implements Reportable {
  const MAX_LINE_LENGTH = 80 - 4;

  public $sentences;

  function __construct(array $sentences) {
    $this->sentences = $sentences;
  }

  public function print(Cursor $cursor, ReportOptions $options): Cursor {
    $cursor->reset();

    $words = [];
    foreach ($this->sentences as $sentence) {
      $words = array_merge($words, explode(' ', $sentence));
    }

    $pending_line = '';
    $space_left = Paragraph::MAX_LINE_LENGTH;
    foreach ($words as $word) {
      if (strlen($word) + 1 > $space_left) {
        $cursor
          ->spaces(2)
          ->text($pending_line)
          ->newline();
        $pending_line = $word;
        $space_left = Paragraph::MAX_LINE_LENGTH - strlen($word);
      } else {
        if (strlen($pending_line) === 0) {
          $pending_line .= $word;
          $space_left -= strlen($word);
        } else {
          $pending_line .= " $word";
          $space_left -= strlen($word) + 1;
        }
      }
    }

    if ($pending_line !== '') {
      $cursor
        ->spaces(2)
        ->text($pending_line)
        ->newline();
    }

    return $cursor;
  }
}
