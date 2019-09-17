<?php

namespace Cthulhu\lib\diff;

class Diff {
  private static function internal_diff(array $before, array $after) {
    $matrix = [];
    $maxlen = 0;

    foreach ($before as $before_index => $before_value) {
      $after_keys = array_keys($after, $before_value);
      foreach ($after_keys as $after_index) {
        $matrix[$before_index][$after_index] = isset($matrix[$before_index - 1][$after_index - 1])
          ? $matrix[$before_index - 1][$after_index - 1] + 1
          : 1;

        if ($matrix[$before_index][$after_index] > $maxlen) {
          $maxlen = $matrix[$before_index][$after_index];
          $before_max = $before_index + 1 - $maxlen;
          $after_max  = $after_index  + 1 - $maxlen;
        }
      }
    }

    if ($maxlen === 0) {
      return [ [ 'del' => $before, 'ins' => $after ] ];
    }

    return array_merge(
      self::internal_diff(
        array_slice($before, 0, $before_max),
        array_slice($after, 0, $after_max)
      ),
      array_slice($after, $after_max, $maxlen),
      self::internal_diff(
        array_slice($before, $before_max + $maxlen),
        array_slice($after, $after_max + $maxlen)
      )
    );
  }

  static function lines(string $before, string $after): array {
    $before = explode(PHP_EOL, $before);
    $after  = explode(PHP_EOL, $after);
    $lines  = [];
    foreach (self::internal_diff($before, $after) as $d) {
      if (is_array($d)) {
        foreach ($d['del'] as $text) {
          $lines[] = new DeleteLine($text);
        }
        foreach ($d['ins'] as $text) {
          $lines[] = new InsertLine($text);
        }
      } else {
        $lines[] = new KeepLine($d);
      }
    }
    return $lines;
  }
}
