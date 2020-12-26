<?php

namespace Cthulhu\lib\trees;

use Attribute;
use Cthulhu\lib\panic\Panic;

#[Attribute(Attribute::TARGET_METHOD)]
class Enter {
  public array $classes;

  /**
   * @param string $class
   * @param string ...$classes
   */
  public function __construct(string $class, ...$classes) {
    $this->classes = [ $class, ...$classes ];
  }

  public static function matches(\ReflectionAttribute $attr, string $file, int $line): self|null {
    if ($attr->getName() !== self::class) {
      return null;
    }

    if (empty($attr->getArguments())) {
      $msg = self::class . " attribute needs to match at least 1 kind of node";
      Panic::with_reason($line, $file, $msg);
    }

    /** @noinspection PhpIncompatibleReturnTypeInspection */
    return $attr->newInstance();
  }
}
