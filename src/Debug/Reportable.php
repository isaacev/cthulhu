<?php

namespace Cthulhu\Debug;

interface Reportable {
  public function print(Cursor $cursor, ReportOptions $options): Cursor;
}
