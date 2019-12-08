<?php

namespace Cthulhu\Debug;

use Cthulhu\lib\fmt\Formatter;

class Cycle implements Reportable {
  public int $index;
  public array $members;

  function __construct(int $index, array $members) {
    $this->index   = $index;
    $this->members = $members;
  }

  public function print(Formatter $f): Formatter {
    $total = count($this->members);
    for ($i = 0; $i <= $total; $i++) {
      $f->newline_if_not_already()
        ->tab();

      $member = $this->members[$i];
      if ($i < $this->index) {
        $f->printf('    %s', $member);
      } else if ($i === $this->index) {
        $f->printf('.-> %s', $member);
      } else if ($i < $total) {
        $f->printf('|   %s', $member);
      } else {
        $f->printf('\'-<-\'');
      }
    }

    return $f;
  }
}
