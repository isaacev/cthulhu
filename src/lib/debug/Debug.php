<?php

namespace Cthulhu\lib\debug;

use Cthulhu\lib\cli\Lookup;

class Debug {
  public const DEBUG_VAR = 'DEBUG';
  private static ?bool $in_debug_mode = null;

  public static function setup(Lookup $options): void {
    $opt_val = $options->get('debug');
    $env_val = getenv(self::DEBUG_VAR);

    if ($opt_val !== null) {
      $opt_val ? self::turn_on() : self::turn_off();
    } else if ($env_val !== null) {
      $env_val ? self::turn_on() : self::turn_off();
    } else {
      self::turn_off();
    }
  }

  public static function turn_on(): void {
    self::$in_debug_mode = true;
  }

  public static function turn_off(): void {
    self::$in_debug_mode = false;
  }

  /**
   * Returns true iff the compiler is running in debug mode.
   *
   * @return bool
   */
  public static function is_true(): bool {
    return self::$in_debug_mode === true;
  }

  /**
   * Returns false iff the compiler is running in debug mode.
   *
   * @return bool
   */
  public static function is_false(): bool {
    return !self::is_true();
  }
}
