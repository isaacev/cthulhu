<?php

namespace Cthulhu\ir\nodes;

use Cthulhu\lib\fmt\Buildable;
use Cthulhu\lib\trees\DefaultMetadata;
use Cthulhu\lib\trees\DefaultUniqueId;
use Cthulhu\lib\trees\EditableNodelike;
use Cthulhu\lib\trees\HasMetadata;

abstract class Node implements EditableNodelike, HasMetadata, Buildable {
  use DefaultUniqueId;
  use DefaultMetadata;

  abstract public function build(): Builder;
}
