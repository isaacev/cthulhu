<?php

namespace Cthulhu\workspace;

use Cthulhu\ast\DeepParser;
use Cthulhu\ast\Errors;
use Cthulhu\ast\Loader;
use Cthulhu\ast\ShallowResolver;
use Cthulhu\err\Error;
use Cthulhu\loc\File;

class LoadPhase {
  private string $relative_filepath;
  private array $options;

  /**
   * @param string   $relative_filepath
   * @param string[] $options
   */
  public function __construct(string $relative_filepath, array $options) {
    $this->relative_filepath = $relative_filepath;
    $this->options           = $options;
  }

  /**
   * @return CheckPhase
   * @throws Error
   */
  public function load(): CheckPhase {
    $loader   = new Loader($this->options);
    $shallow  = $loader->from_string($this->relative_filepath);
    $bindings = ShallowResolver::resolve($shallow);
    $deep     = (new DeepParser($shallow))->program();
    return new CheckPhase($bindings, $deep);
  }

  /**
   * @param File $file
   * @return CheckPhase
   * @throws Error
   */
  public static function from_memory(File $file): CheckPhase {
    $loader   = new Loader([
      'path' => [
        realpath(__DIR__ . '/../stdlib'),
      ],
    ]);
    $shallow  = $loader->from_file($file);
    $bindings = ShallowResolver::resolve($shallow);
    $deep     = (new DeepParser($shallow))->program();
    return new CheckPhase($bindings, $deep);
  }

  /**
   * @param string $filepath
   * @return CheckPhase
   * @throws Error
   */
  public static function from_filepath(string $filepath): CheckPhase {
    $absolute_filepath = realpath($filepath);

    if ($absolute_filepath === false) {
      throw Errors::unable_to_read_file($filepath);
    }

    $absolute_directory_path = dirname($absolute_filepath);
    $filename                = basename($absolute_filepath, '.cth');

    $loader = new LoadPhase($filename, [
      'path' => [
        $absolute_directory_path,
        realpath(__DIR__ . '/../stdlib'),
      ],
    ]);

    return $loader->load();
  }
}
