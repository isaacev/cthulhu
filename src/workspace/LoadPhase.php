<?php

namespace Cthulhu\workspace;

use Cthulhu\ast\DeepParser;
use Cthulhu\ast\Loader;
use Cthulhu\ast\ShallowResolver;
use Cthulhu\err\Error;
use Cthulhu\loc\Directory;
use Cthulhu\loc\File;

class LoadPhase {
  /**
   * @param File $origin
   * @return CheckPhase
   * @throws Error
   */
  public static function from_file(File $origin): CheckPhase {
    $stdlib   = Directory::stdlib();
    $loader   = new Loader($stdlib);
    $shallow  = $loader->from_file($origin);
    $bindings = ShallowResolver::resolve($shallow);
    $deep     = (new DeepParser($shallow))->program();
    return new CheckPhase($bindings, $deep);
  }

  /**
   * @param string $relative
   * @return CheckPhase
   * @throws Error
   */
  public static function from_filepath(string $relative): CheckPhase {
    $origin = File::from_relative_filepath($relative);
    return self::from_file($origin);
  }
}
