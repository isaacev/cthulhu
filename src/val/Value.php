<?php

namespace Cthulhu\val;

abstract class Value {
  abstract public function encode_as_php(): string;
}
