<?php

namespace Cthulhu\utils\cli\internals;

class Helper {
  static function aligned_descriptions(Describeable ...$pairs): void {
    $max_width = 0;
    foreach ($pairs as $pair) {
      $max_width = max($max_width, strlen($pair->full_name()));
    }

    foreach ($pairs as $pair) {
      printf("    %-${max_width}s    %s\n",
        $pair->full_name(),
        $pair->description);
    }
  }

  static function section(string $title, Describeable ...$pairs): void {
    printf("%s:\n", strtoupper($title));

    if (empty($pairs)) {
      printf("    no %s\n", strtolower($title));
    } else {
      self::aligned_descriptions(...$pairs);
    }

    echo PHP_EOL;
  }

  static function usage(string ...$segments): void {
    echo "USAGE:\n";
    printf("    %s\n\n", implode(' ', $segments));
  }
}
