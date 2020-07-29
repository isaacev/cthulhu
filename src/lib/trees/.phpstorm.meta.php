<?php

namespace PHPSTORM_META {
  override(\Cthulhu\lib\trees\HasMetadata::get(0), map([
    \Cthulhu\loc\Span::KEY => \Cthulhu\loc\Span::class,
    \Cthulhu\ir\names\Symbol::KEY => \Cthulhu\ir\names\Symbol::class,
  ]));
}
