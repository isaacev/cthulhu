<?php

namespace Cthulhu\workspace;

use Cthulhu\Errors\Error;

class Errors {
  public static function unable_to_read_file(string $filepath): Error {
    return (new Error('unable to read file'))
      ->paragraph("Either the following file does not exist or this process is not allowed to read it:")
      ->paragraph($filepath);
  }
}
