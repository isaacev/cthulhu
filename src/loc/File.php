<?php

namespace Cthulhu\loc;

use Cthulhu\ast\Errors;
use Cthulhu\err\Error;

class File {
  public Filepath $filepath;
  public string $contents;

  public function __construct(Filepath $filepath, string $contents) {
    $this->filepath = $filepath;
    $this->contents = $contents;
  }

  public function basename(): string {
    return $this->filepath->filename;
  }

  /**
   * @param string $relative
   * @return static
   * @throws Error
   */
  public static function from_relative_filepath(string $relative): self {
    if (($absolute = realpath($relative)) === false) {
      throw Errors::unable_to_read_file($relative);
    }

    $info      = pathinfo($absolute);
    $directory = new Directory($info["dirname"], false);
    $filepath  = new Filepath($directory, $info["filename"], $info["extension"]);
    return $filepath->to_file();
  }
}
