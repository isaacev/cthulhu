<?php

namespace Cthulhu\php\nodes;

use Cthulhu\lib\fmt;
use Cthulhu\lib\trees\DefaultMetadata;
use Cthulhu\lib\trees\DefaultUniqueId;
use Cthulhu\lib\trees\EditableNodelike;
use Cthulhu\lib\trees\HasMetadata;
use Cthulhu\php\Builder;

abstract class Node implements EditableNodelike, HasMetadata, fmt\Buildable {
  use DefaultUniqueId;
  use DefaultMetadata;

  abstract public function build(): Builder;
}
