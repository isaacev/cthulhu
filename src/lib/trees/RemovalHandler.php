<?php

namespace Cthulhu\lib\trees;

interface RemovalHandler {
  public function handle_removal(): ?Nodelike;
}
