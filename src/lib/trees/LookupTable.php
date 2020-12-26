<?php

namespace Cthulhu\lib\trees;

interface LookupTable {
  public function preorder(Nodelike $node, mixed ...$args): void;

  public function postorder(Nodelike $node, mixed ...$args): void;
}
