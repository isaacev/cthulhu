<?php

namespace Cthulhu\workspace;

use Cthulhu\err\Error;
use Cthulhu\loc\File;

class ReadPhase {
  /**
   * @param string $filepath
   * @return ParsePhase
   * @throws Error
   */
  public static function from_file_system(string $filepath): ParsePhase {
    $contents = @file_get_contents($filepath);
    if ($contents === false) {
      throw Errors::unable_to_read_file($filepath);
    }
    $file = new File($filepath, $contents);
    return new ParsePhase($file);
  }

  public static function from_memory(File $file): ParsePhase {
    return new ParsePhase($file);
  }
}
