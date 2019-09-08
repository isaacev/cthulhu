<?php

namespace Cthulhu\Debug;

class Paragraph implements Reportable {
  const MAX_LINE_LENGTH = 80 - 4;

  public $sentences;

  function __construct(array $sentences) {
    $this->sentences = $sentences;
  }

  public function print(Teletype $tty): Teletype {
    $combined_sentences = implode(' ', $this->sentences);
    $words = preg_split('/\s+/', $combined_sentences);
    $word_count = count($words);
    $word_index = 0;

    while ($word_index < $word_count) {
      $word = $words[$word_index++];
      $tty
        ->newline_if_not_empty()
        ->tab()
        ->printf($word);

      while ($word_index < $word_count) {
        // Don't increment the `word_index` here because this word might have to
        // be pushed to the next line if it doesn't fit.
        $next_word = $words[$word_index];

        if ($tty->space_left_on_line() < strlen($next_word) + 1) {
          // There isn't enough space on the current line for the next word and
          // the space before it so move to the next line.
          continue 2;
        }

        // If the word first, increment the index and print the word.
        $word_index++;
        $tty
          ->spaces(1)
          ->printf($next_word);
      }
    }

    return $tty;
  }
}
