<?php

namespace Cthulhu\workspace;

use Cthulhu\Source\File;

class ReadPhase {
  static function from_file_system(string $filepath): ParsePhase {
    $contents = @file_get_contents($filepath);
    if ($contents === false) {
      throw Errors::unable_to_read_file($filepath);
    }
    $file = new File($filepath, $contents);
    return new ParsePhase($file);
  }

  static function from_memory(File $file): ParsePhase {
    return new ParsePhase($file);
  }
}