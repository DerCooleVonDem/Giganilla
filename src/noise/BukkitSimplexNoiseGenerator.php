<?php

namespace JonasWindmann\Giganilla\noise;

use JonasWindmann\Giganilla\GigaMath;
use JonasWindmann\Giganilla\GigaRandom;

class BukkitSimplexNoiseGenerator {

    private int|float $offsetX;
    private int|float $offsetY;
    private array $permutations = [];
    private array $grad3 = [
        [-1, 1], [-1, -1], [1, -1], [1, 1],
        [-1, 0], [0, -1], [1, 0], [0, 1],
        [-1, 1], [-1, -1], [1, -1], [1, 1],
        [-1, 0], [0, -1], [1, 0], [0, 1]
    ];

    const SQRT_3 = 1.7320508075688772;
    const F2 = 0.5 * (self::SQRT_3 - 1);
    const G2 = (3 - self::SQRT_3) / 6;
    const G22 = self::G2 * 2.0 - 1;

    public function __construct(GigaRandom $random) {
        $this->offsetX = $random->nextFloat() * 256;
        $this->offsetY = $random->nextFloat() * 256;
        $random->nextSignedInt(); // Generate offsetZ

        // Initialize permutations array
        for ($i = 0; $i < 256; ++$i) {
            $this->permutations[$i] = $random->nextIntWithBound(256);
        }

        // Shuffle permutations array
        for ($i = 0; $i < 256; ++$i) {
            $pos = $random->nextIntWithBound(256 - $i) + $i;
            $old = $this->permutations[$i];

            $this->permutations[$i] = $this->permutations[$pos];
            $this->permutations[$pos] = $old;
            $this->permutations[$i + 256] = $this->permutations[$i];
        }

        $random->nextSignedInt(); // Generate offsetW
    }

    public function simplex2d(float $xin, float $yin): float
    {
        $xin += $this->offsetX;
        $yin += $this->offsetY;

        $n0 = $n1 = $n2 = 0; // Initialize noise contributions

        // Skew input space to determine simplex cell
        $s = ($xin + $yin) * self::F2;
        $i = GigaMath::floorSimplex($xin + $s);
        $j = GigaMath::floorSimplex($yin + $s);
        $t = ($i + $j) * self::G2;
        $X0 = $i - $t; // Unskew cell origin
        $Y0 = $j - $t;
        $x0 = $xin - $X0; // Distances from cell origin
        $y0 = $yin - $Y0;

        // Determine simplex corner offsets
        $i1 = $j1 = 0;
        if ($x0 > $y0) {
            $i1 = 1;
            $j1 = 0;
        } else {
            $i1 = 0;
            $j1 = 1;
        }

        // Calculate offsets for middle and last corners
        $x1 = $x0 - $i1 + self::G2;
        $y1 = $y0 - $j1 + self::G2;
        $x2 = $x0 + self::G22;
        $y2 = $y0 + self::G22;

        // Calculate gradient indices
        $ii = $i & 255;
        $jj = $j & 255;
        $gi0 = $this->permutations[$ii + $this->permutations[$jj]] % 12;
        $gi1 = $this->permutations[$ii + $i1 + $this->permutations[$jj + $j1]] % 12;
        $gi2 = $this->permutations[$ii + 1 + $this->permutations[$jj + 1]] % 12;

        // Calculate contributions from corners
        $t0 = 0.5 - $x0 * $x0 - $y0 * $y0;
        $n0 = $t0 < 0 ? 0.0 : $t0 * $t0 * $this->dot($this->grad3[$gi0], $x0, $y0);

        $t1 = 0.5 - $x1 * $x1 - $y1 * $y1;
        $n1 = $t1 < 0 ? 0.0 : $t1 * $t1 * $this->dot($this->grad3[$gi1], $x1, $y1);

        $t2 = 0.5 - $x2 * $x2 - $y2 * $y2;
        $n2 = $t2 < 0 ? 0.0 : $t2 * $t2 * $this->dot($this->grad3[$gi2], $x2, $y2);

        // Return final noise value
        return 70.0 * ($n0 + $n1 + $n2);
    }

    private function dot($grad, $x, $y): float|int
    {
        return $grad[0] * $x + $grad[1] * $y;
    }
}