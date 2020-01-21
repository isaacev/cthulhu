<?php

namespace Cthulhu\ir\nodes2;

use Cthulhu\ir\types\traits\DefaultWalkable;
use Cthulhu\lib\fmt\Buildable;
use Cthulhu\lib\trees\DefaultMetadata;
use Cthulhu\lib\trees\DefaultUniqueId;
use Cthulhu\lib\trees\EditableNodelike;
use Cthulhu\lib\trees\HasMetadata;

abstract class Node implements EditableNodelike, HasMetadata, Buildable {
  use DefaultUniqueId;
  use DefaultWalkable;
  use DefaultMetadata;

  abstract public function build(): Builder;
}
