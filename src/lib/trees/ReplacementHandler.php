<?php

namespace Cthulhu\lib\trees;

interface ReplacementHandler {
  public function handle_replacement(Nodelike $replacement): Nodelike;
}
