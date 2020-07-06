<?php

namespace Cthulhu\lib\panic;

class Panic {
  /**
   * @param int    $line
   * @param string $file
   * @param string $reason
   * @param int    $code
   * @return int
   * @noinspection PhpUnreachableStatementInspection
   */
  public static function with_reason(int $line, string $file, string $reason, int $code = 1) {
    fprintf(STDERR, "(internal error at line $line in $file) $reason\n");
    die($code);
    return $code;
  }

  /**
   * @param int    $line
   * @param string $file
   * @param mixed  $thing
   * @return int
   */
  public static function if_reached(int $line, string $file, $thing = null): int {
    if ($thing === null) {
      return self::with_reason($line, $file, 'unreachable');
    } else {
      return self::with_reason($line, $file, 'unsupported kind: ' . get_class($thing));
    }
  }
}
