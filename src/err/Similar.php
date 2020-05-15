<?php

namespace Cthulhu\err;

use Cthulhu\lib\fmt\Formatter;

class Similar implements Reportable {
  public string $sentence;
  public array $candidates;

  /**
   * @param string   $sentence
   * @param string   $incorrect
   * @param string[] $fixes
   */
  public function __construct(string $sentence, string $incorrect, array $fixes) {
    $this->sentence   = $sentence;
    $this->candidates = self::find_candidates($incorrect, $fixes);
  }

  public function print(Formatter $f): ?Formatter {
    if (empty($this->candidates)) {
      return null;
    }

    $f->newline_if_not_already()
      ->tab()
      ->text_wrap($this->sentence)
      ->newline_if_not_already()
      ->increment_tab_stop(2);

    foreach ($this->candidates as $candidate) {
      $f->newline()
        ->tab()
        ->print($candidate);
    }

    return $f->pop_tab_stop();
  }

  /**
   * @param string $incorrect
   * @param array  $fixes
   * @param int    $take
   * @return string[]
   */
  public static function find_candidates(string $incorrect, array $fixes, int $take = 3): array {
    // Determine the edit distance between the `$incorrect` string and each of
    // the `$fixes`. Store these costs in a mapping from `fix -> cost`
    $costs = [];
    foreach ($fixes as $fix) {
      $cost        = self::damerau_levenshtein_distance($incorrect, $fix);
      $costs[$fix] = $cost;
    }

    // Sort the key/value pairs from smallest edit distance -> largest
    asort($costs);
    $sorted_fixes = array_keys($costs);

    // Return the `$take` candidates with the smallest edit distances
    return array_slice($sorted_fixes, 0, $take);
  }

  /**
   * @link https://en.wikipedia.org/wiki/Damerau%E2%80%93Levenshtein_distance
   * @link https://gist.github.com/gekh/330266fc9c776a9509ecc1b0186c40f3
   *
   * @param string $src
   * @param string $dest
   * @return int
   */
  private static function damerau_levenshtein_distance(string $src, string $dest): int {
    if ($src == $dest) {
      return 0;
    }

    $src_len  = strlen($src);
    $dest_len = strlen($dest);

    if ($src_len == 0) {
      return $dest_len;
    } else if ($dest_len == 0) {
      return $src_len;
    }

    $j             = 0;
    $prev_row      = range(0, $dest_len);
    $prev_prev_row = null;
    for ($i = 0; $i < $src_len; $i++) {
      $this_row  = [ $i + 1 ];
      $char      = $src[$i];
      $prev_char = $src[$i - 1];

      for ($j = 0; $j < $dest_len; $j++) {
        $cost             = $char === $dest[$j] ? 0 : 1;
        $deletion         = $prev_row[$j + 1] + 1;
        $insertion        = $this_row[$j] + 1;
        $substitution     = $prev_row[$j] + $cost;
        $this_row[$j + 1] = min($deletion, $insertion, $substitution);

        if ($i > 0 && $j > 0 && $char == $dest[$j - 1] && $prev_char == $dest[$j]) {
          $transposition    = $prev_prev_row[$j - 1] + $cost;
          $this_row[$j + 1] = min($this_row[$j + 1], $transposition);
        }
      }

      $prev_prev_row = $prev_row;
      $prev_row      = $this_row;
    }

    return $prev_row[$j];
  }
}
