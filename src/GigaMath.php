<?php

namespace JonasWindmann\Giganilla;

class GigaMath {

    public static function floor(float $x): int
    {
        $floored = (int) $x;
        return $x < $floored ? $floored - 1 : $floored;
    }

    public static function lerp(float $delta, float $start, float $end): float|int
    {
        return $start + $delta * ($end - $start);
    }

    public static function grad(float $hash, float $x, float $y, float $z): float
    {
        $hash &= 15;
        $u = $hash < 8 ? $x : $y;
        $v = $hash < 4 ? $y : ($hash == 12 || $hash == 14 ? $x : $z);
        return (($hash & 1) == 0 ? $u : -$u) + (($hash & 2) == 0 ? $v : -$v);
    }

    public static function fade(float $x): float|int
    {
        return $x * $x * $x * ($x * ($x * 6 - 15) + 10);
    }

    public static function floorSimplex(float $x): int
    {
        return $x > 0 ? (int) $x : (int) $x - 1;
    }

    public static function dot(array $g, float $x, float $y): float
    {
        return $g[0] * $x + $g[1] * $y;
    }

    public static function dot3(array $g, float $x, float $y, float $z): float
    {
        return $g[0] * $x + $g[1] * $y + $g[2] * $z;
    }

    public static function floorLong(float $x): int
    {
        return $x >= 0 ? (int) $x : (int) $x - 1;
    }
}