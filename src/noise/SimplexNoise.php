<?php

namespace JonasWindmann\Giganilla\noise;

use JonasWindmann\Giganilla\GigaMath;
use JonasWindmann\Giganilla\GigaRandom;

class SimplexNoise extends PerlinNoise {
    const SQRT_3 = 1.7320508075688772;
    const F2 = 0.5 * (self::SQRT_3 - 1);
    const G2 = (3 - self::SQRT_3) / 6;
    const G22 = self::G2 * 2.0 - 1;
    const F3 = 1.0 / 3.0;
    const G3 = 1.0 / 6.0;
    const G32 = self::G3 * 2.0;
    const G33 = self::G3 * 3.0 - 1.0;

    private array $permMod12 = [];

    private array $grad3 = [
        [1, 1, 0],
        [-1, 1, 0],
        [1, -1, 0],
        [-1, -1, 0],
        [1, 0, 1],
        [-1, 0, 1],
        [1, 0, -1],
        [-1, 0, -1],
        [0, 1, 1],
        [0, -1, 1],
        [0, 1, -1],
        [0, -1, -1]
    ];

    public function __construct(GigaRandom $random) {
        parent::__construct($random);

        for ($i = 0; $i < 512; $i++) {
            $this->permMod12[$i] = $this->permutations[$i] % 12;
        }
    }

    public function get2dNoise(array &$noise, float $x, float $z, int $sizeX, int $sizeY, float $scaleX, float $scaleY, float $amplitude): void {
        $index = 0;
        for ($i = 0; $i < $sizeY; $i++) {
            $zin = $this->offsetY + ($z + $i) * $scaleY;
            for ($j = 0; $j < $sizeX; $j++) {
                $xin = $this->offsetX + ($x + $j) * $scaleX;
                $noise[$index++] += $this->simplex2d($xin, $zin) * $amplitude;
            }
        }
    }

    public function get3dNoise(array &$noise, float $x, float $y, float $z, int $sizeX, int $sizeY, int $sizeZ, float $scaleX, float $scaleY, float $scaleZ, float $amplitude): void {
        $index = 0;
        for ($i = 0; $i < $sizeZ; $i++) {
            $zin = $this->offsetZ + ($z + $i) * $scaleZ;
            for ($j = 0; $j < $sizeX; $j++) {
                $xin = $this->offsetX + ($x + $j) * $scaleX;
                for ($k = 0; $k < $sizeY; $k++) {
                    $yin = $this->offsetY + ($y + $k) * $scaleY;
                    $noise[$index++] += $this->simplex3d($xin, $yin, $zin) * $amplitude;
                }
            }
        }
    }

    public function simplex2d(float $xin, float $yin): float {
        $s = ($xin + $yin) * self::F2; // Hairy factor for 2D
        $i = GigaMath::FloorSimplex($xin + $s);
        $j = GigaMath::FloorSimplex($yin + $s);
        $t = ($i + $j) * self::G2;
        $dx0 = $i - $t; // Unskew the cell origin back to (x,y) space
        $dy0 = $j - $t;
        $x0 = $xin - $dx0; // The x,y distances from the cell origin
        $y0 = $yin - $dy0;

        // Determine which simplex we are in.
        $i1 = 0; // Offsets for second (middle) corner of simplex in (i,j) coords
        $j1 = 0;
        if ($x0 > $y0) {
            $i1 = 1; // lower triangle, XY order: (0,0)->(1,0)->(1,1)
            $j1 = 0;
        } else {
            $i1 = 0; // upper triangle, YX order: (0,0)->(0,1)->(1,1)
            $j1 = 1;
        }

        $x1 = $x0 - $i1 + self::G2; // Offsets for middle corner in (x,y) unskewed coords
        $y1 = $y0 - $j1 + self::G2;
        $x2 = $x0 + self::G22; // Offsets for last corner in (x,y) unskewed coords
        $y2 = $y0 + self::G22;

        // Work out the hashed gradient indices of the three simplex corners
        $ii = $i & 255;
        $jj = $j & 255;
        $gi0 = $this->permMod12[$ii + $this->permutations[$jj]];
        $gi1 = $this->permMod12[$ii + $i1 + $this->permutations[$jj + $j1]];
        $gi2 = $this->permMod12[$ii + 1 + $this->permutations[$jj + 1]];

        // Calculate the contribution from the three corners
        $t0 = 0.5 - $x0 * $x0 - $y0 * $y0;
        $n0 = ($t0 < 0) ? 0.0 : $t0 * $t0 * GigaMath::Dot($this->grad3[$gi0], $x0, $y0); // (x,y) of grad3 used for 2D gradient

        $t1 = 0.5 - $x1 * $x1 - $y1 * $y1;
        $n1 = ($t1 < 0) ? 0.0 : $t1 * $t1 * GigaMath::Dot($this->grad3[$gi1], $x1, $y1);

        $t2 = 0.5 - $x2 * $x2 - $y2 * $y2;
        $n2 = ($t2 < 0) ? 0.0 : $t2 * $t2 * GigaMath::Dot($this->grad3[$gi2], $x2, $y2);

        // Add contributions from each corner to get the final noise value.
        // The result is scaled to return values in the interval [-1,1].
        return 70.0 * ($n0 + $n1 + $n2);
    }

    public function simplex3d(float $xin, float $yin, float $zin): float {
        // Skew the input space to determine which simplex cell we're in
        $s = ($xin + $yin + $zin) * self::F3; // Very nice and simple skew factor for 3D
        $i = floor($xin + $s);
        $j = floor($yin + $s);
        $k = floor($zin + $s);
        $t = ($i + $j + $k) * self::G3;
        $dx0 = $i - $t; // Unskew the cell origin back to (x,y,z) space
        $dy0 = $j - $t;
        $dz0 = $k - $t;

        // For the 3D case, the simplex shape is a slightly irregular tetrahedron.

        $i1 = 0; // Offsets for second corner of simplex in (i,j,k) coords
        $j1 = 0;
        $k1 = 0;
        $i2 = 0; // Offsets for third corner of simplex in (i,j,k) coords
        $j2 = 0;
        $k2 = 0;

        $x0 = $xin - $dx0; // The x,y,z distances from the cell origin
        $y0 = $yin - $dy0;
        $z0 = $zin - $dz0;
        // Determine which simplex we are in
        if ($x0 >= $y0) {
            if ($y0 >= $z0) {
                $i1 = 1; // X Y Z order
                $j1 = 0;
                $k1 = 0;
                $i2 = 1;
                $j2 = 1;
                $k2 = 0;
            } else if ($x0 >= $z0) {
                $i1 = 1; // X Z Y order
                $j1 = 0;
                $k1 = 0;
                $i2 = 1;
                $j2 = 0;
                $k2 = 1;
            } else {
                $i1 = 0; // Z X Y order
                $j1 = 0;
                $k1 = 1;
                $i2 = 1;
                $j2 = 0;
                $k2 = 1;
            }
        } else { // x0<y0
            if ($y0 < $z0) {
                $i1 = 0; // Z Y X order
                $j1 = 0;
                $k1 = 1;
                $i2 = 0;
                $j2 = 1;
                $k2 = 1;
            } else if ($x0 < $z0) {
                $i1 = 0; // Y Z X order
                $j1 = 1;
                $k1 = 0;
                $i2 = 0;
                $j2 = 1;
                $k2 = 1;
            } else {
                $i1 = 0; // Y X Z order
                $j1 = 1;
                $k1 = 0;
                $i2 = 1;
                $j2 = 1;
                $k2 = 0;
            }
        }

        // A step of (1,0,0) in (i,j,k) means a step of (1-c,-c,-c) in (x,y,z),
        // a step of (0,1,0) in (i,j,k) means a step of (-c,1-c,-c) in (x,y,z), and
        // a step of (0,0,1) in (i,j,k) means a step of (-c,-c,1-c) in (x,y,z), where
        // c = 1/6.
        $x1 = $x0 - $i1 + self::G3; // Offsets for second corner in (x,y,z) coords
        $y1 = $y0 - $j1 + self::G3;
        $z1 = $z0 - $k1 + self::G3;
        $x2 = $x0 - $i2 + self::G32; // Offsets for third corner in (x,y,z) coords
        $y2 = $y0 - $j2 + self::G32;
        $z2 = $z0 - $k2 + self::G32;

        // Work out the hashed gradient indices of the four simplex corners
        $ii = $i & 255;
        $jj = $j & 255;
        $kk = $k & 255;
        $gi0 = $this->permMod12[$ii + $this->permutations[$jj + $this->permutations[$kk]]];
        $gi1 = $this->permMod12[$ii + $i1 + $this->permutations[$jj + $j1 + $this->permutations[$kk + $k1]]];
        $gi2 = $this->permMod12[$ii + $i2 + $this->permutations[$jj + $j2 + $this->permutations[$kk + $k2]]];
        $gi3 = $this->permMod12[$ii + 1 + $this->permutations[$jj + 1 + $this->permutations[$kk + 1]]];

        // Calculate the contribution from the four corners
        $t0 = 0.5 - $x0 * $x0 - $y0 * $y0 - $z0 * $z0;
        $n0 = 0; // Noise contributions from the four corners
        if ($t0 < 0) {
            $n0 = 0.0;
        } else {
            $t0 *= $t0;
            $n0 = $t0 * $t0 * GigaMath::Dot3($this->grad3[$gi0], $x0, $y0, $z0);
        }

        $t1 = 0.5 - $x1 * $x1 - $y1 * $y1 - $z1 * $z1;
        $n1 = 0;
        if ($t1 < 0) {
            $n1 = 0.0;
        } else {
            $t1 *= $t1;
            $n1 = $t1 * $t1 * GigaMath::Dot3($this->grad3[$gi1], $x1, $y1, $z1);
        }

        $t2 = 0.5 - $x2 * $x2 - $y2 * $y2 - $z2 * $z2;
        $n2 = 0;
        if ($t2 < 0) {
            $n2 = 0.0;
        } else {
            $t2 *= $t2;
            $n2 = $t2 * $t2 * GigaMath::Dot3($this->grad3[$gi2], $x2, $y2, $z2);
        }

        $x3 = $x0 + self::G33; // Offsets for last corner in (x,y,z) coords
        $y3 = $y0 + self::G33;
        $z3 = $z0 + self::G33;
        $t3 = 0.5 - $x3 * $x3 - $y3 * $y3 - $z3 * $z3;
        $n3 = 0;
        if ($t3 < 0) {
            $n3 = 0.0;
        } else {
            $t3 *= $t3;
            $n3 = $t3 * $t3 * GigaMath::Dot3($this->grad3[$gi3], $x3, $y3, $z3);
        }

        // Add contributions from each corner to get the final noise value.
        // The result is scaled to stay just inside [-1,1]
        return 32.0 * ($n0 + $n1 + $n2 + $n3);
    }

    public function noise3d(float $xin, float $yin, float $zin): float {
        $xin += $this->offsetX;
        $yin += $this->offsetZ;
        if ($xin == 0.0) {
            return $this->simplex2d($xin, $yin);
        }

        $zin += $this->offsetZ;
        return $this->simplex3d($xin, $yin, $zin);
    }
}
