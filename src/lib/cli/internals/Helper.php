<?php

namespace Cthulhu\lib\cli\internals;

use Cthulhu\lib\fmt\Formatter;

class Helper {
  public const COLUMN_SPACER = 4;

  public static function aligned_descriptions(Formatter $f, Describeable ...$pairs): void {
    $max_width = 0;
    foreach ($pairs as $pair) {
      $max_width = max($max_width, strlen($pair->full_name()));
    }

    $f->push_tab_stop(self::COLUMN_SPACER);
    foreach ($pairs as $pair) {
      $f->tab()
        ->print($pair->full_name())
        ->increment_tab_stop($max_width + self::COLUMN_SPACER)
        ->tab()
        ->text_wrap($pair->description())
        ->pop_tab_stop()
        ->newline();
    }
    $f->pop_tab_stop();
  }

  public static function section(Formatter $f, string $title, Describeable ...$pairs): void {
    $f->printf('%s:', strtoupper($title))
      ->newline();

    if (empty($pairs)) {
      $f->tab_to(self::COLUMN_SPACER)
        ->printf('no %s', strtolower($title))
        ->newline();
    } else {
      self::aligned_descriptions($f, ...$pairs);
    }
  }

  public static function usage(Formatter $f, string ...$segments): void {
    $f->print('USAGE:')
      ->newline()
      ->tab_to(self::COLUMN_SPACER)
      ->print(implode(' ', $segments))
      ->newline();
  }
}
