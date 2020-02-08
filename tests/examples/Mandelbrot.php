<?php

namespace Mandelbrot {
  function range($a, $b, $c) {
    while (true) {
      $n = $a;
      $max = $b;
      $f = $c;
      if ($n < $max) {
        $f($n);
        $a = $n + 1.0;
        $b = $max;
        $c = $f;
        continue;
      } else {
        return;
      }
    }
  }
  function real($i, $w, $h) {
    $scalar = 3.0;
    return $scalar * $i / ($w - 1.0) - $scalar / 2.0;
  }
  function imaginary($j, $w, $h) {
    $scalar = 3.0;
    return $scalar * $j / ($h - 1.0) - $scalar / 2.0;
  }
  function in_set($a, $b, $c, $d, $e) {
    while (true) {
      $n = $a;
      $cr = $b;
      $ci = $c;
      $zr = $d;
      $zi = $e;
      if ($n <= 0) {
        return 0;
      } else {
        $zr2 = \pow($zr, 2.0) - \pow($zi, 2.0) + $cr;
        $zi2 = 2.0 * $zi * $zr + $ci;
        if (\sqrt(\pow($zr2, 2.0) + \pow($zi2, 2.0)) > 2.0) {
          return $n;
        } else {
          $a = $n - 1;
          $b = $cr;
          $c = $ci;
          $d = $zr2;
          $e = $zi2;
          continue;
        }
      }
    }
  }
  function main() {
    $h = 59.0;
    $w = 120.0;
    $t = 1000;
    \Mandelbrot\range(0.0, $h, function($i) use ($w, $h, $t) {
      \Mandelbrot\range(0.0, $w, function($j) use ($w, $h, $i, $t) {
        $re = \Mandelbrot\real($j - 20.0, $w, $h);
        $im = \Mandelbrot\imaginary($i, $w, $h);
        $th = \Mandelbrot\in_set($t, $re, $im, $re, $im);
        if ($th == 0) {
          $ch = " ";
        } else if ($th == 993) {
          $ch = "8";
        } else if ($th == 994) {
          $ch = "%";
        } else if ($th == 995) {
          $ch = "8";
        } else if ($th == 996) {
          $ch = "o";
        } else if ($th == 997) {
          $ch = "+";
        } else if ($th == 998) {
          $ch = "=";
        } else if ($th == 999) {
          $ch = "-";
        } else if ($th == 1000) {
          $ch = " ";
        } else {
          $ch = ".";
        }
        print($ch);
      });
      print("\n");
    });
    return null;
  }
}

namespace {
  \Mandelbrot\main(null);
}
