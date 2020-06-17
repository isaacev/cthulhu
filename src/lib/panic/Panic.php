<?php

namespace Cthulhu\lib\panic;

class Panic {
  /**
   * @param int    $line
   * @param string $file
   * @param string $reason
   * @param int    $code
   */
  public static function with_reason(int $line, string $file, string $reason, int $code = 1) {
    fprintf(STDERR, "(internal error at line $line in $file) $reason\n");
    die($code);
  }

  /**
   * @param int    $line
   * @param string $file
   * @param mixed  $thing
   */
  public static function if_reached(int $line, string $file, $thing = null) {
    if ($thing === null) {
      self::with_reason($line, $file, 'unreachable');
    } else {
      self::with_reason($line, $file, 'unsupported kind: ' . get_class($thing));
    }
  }
}
