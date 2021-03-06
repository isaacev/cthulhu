<?php

namespace Cthulhu\loc;

use Cthulhu\ast\Errors;
use Cthulhu\err\Error;

class Filepath {
  public Directory $directory;

  /**
   * Name of the file WITHOUT the extension.
   * @var string
   */
  public string $filename;

  /**
   * File extension WITHOUT the leading dot. If the file doesn't have an
   * extension, this field should be an empty string.
   * @var string
   */
  public string $extension;

  public function __construct(Directory $directory, string $filename, string $extension) {
    $this->directory = $directory;
    $this->filename  = $filename;
    $this->extension = $extension;
  }

  public function matches(string $filename, string $extension = 'cth'): bool {
    return $this->filename === $filename && $this->matches_extension($extension);
  }

  public function matches_extension(string $extension = 'cth'): bool {
    return $this->extension === $extension;
  }

  /**
   * @return File
   * @throws Error
   */
  public function to_file(): File {
    $contents = @file_get_contents("$this");
    if ($contents === false) {
      throw Errors::unable_to_read_file($this);
    }

    return new File($this, $contents);
  }

  public function __toString(): string {
    return "$this->directory/$this->filename.$this->extension";
  }

  public static function from_directory(Directory $dir, string $filename): self {
    $parts     = pathinfo($filename);
    $filename  = array_key_exists('filename', $parts) ? $parts['filename'] : '';
    $extension = array_key_exists('extension', $parts) ? $parts['extension'] : '';
    return new self($dir, $filename, $extension);
  }
}
