<?php

namespace JonasWindmann\Giganilla\noise;

use JonasWindmann\Giganilla\GigaMath;
use JonasWindmann\Giganilla\GigaRandom;

class PerlinNoise {
    public array $permutations = [];
    public float $offsetX;
    public float $offsetY;
    public float $offsetZ;

    public function __construct(GigaRandom $random) {
        $this->offsetX = $random->nextFloat() * 256;
        $this->offsetY = $random->nextFloat() * 256;
        $this->offsetZ = $random->nextFloat() * 256;

        for ($i = 0; $i < 256; $i++) {
            $this->permutations[$i] = $i;
        }

        for ($i = 0; $i < 256; $i++) {
            $pos = $random->nextIntWithBound(256 - $i) + $i;
            $old = $this->permutations[$i];
            $this->permutations[$i] = $this->permutations[$pos];
            $this->permutations[$pos] = $old;
            $this->permutations[$i + 256] = $this->permutations[$i];
        }
    }

    public function getNoise(array &$noise, float $x, float $y, float $z, int $sizeX, int $sizeY, int $sizeZ, float $scaleX, float $scaleY, float $scaleZ, float $amplitude): void {
        if ($sizeY === 1) {
            $this->get2dNoise($noise, $x, $z, $sizeX, $sizeZ, $scaleX, $scaleZ, $amplitude);
        } else {
            $this->get3dNoise($noise, $x, $y, $z, $sizeX, $sizeY, $sizeZ, $scaleX, $scaleY, $scaleZ, $amplitude);
        }
    }

    private function get2dNoise(array &$noise, float $x, float $z, int $sizeX, int $sizeZ, float $scaleX, float $scaleZ, float $amplitude): void {
        $index = 0;

        for ($i = 0; $i < $sizeX; $i++) {
            $dx = $x + $this->offsetX + $i * $scaleX;
            $floorX = GigaMath::floor($dx);
            $ix = $floorX & 255;
            $dx -= $floorX;
            $fx = GigaMath::fade($dx);
            for ($j = 0; $j < $sizeZ; $j++) {
                $dz = $z + $this->offsetZ + $j * $scaleZ;
                $floorZ = GigaMath::floor($dz);
                $iz = $floorZ & 255;
                $dz -= $floorZ;
                $fz = GigaMath::fade($dz);
                // Hash coordinates of the square corners
                $a = $this->permutations[$ix];
                $aa = $this->permutations[$a] + $iz;
                $b = $this->permutations[$ix + 1];
                $ba = $this->permutations[$b] + $iz;
                $x1 = GigaMath::lerp($fx, GigaMath::grad($this->permutations[$aa], $dx, 0, $dz), GigaMath::grad($this->permutations[$ba], $dx - 1, 0, $dz));
                $x2 = GigaMath::lerp($fx, GigaMath::grad($this->permutations[$aa + 1], $dx, 0, $dz - 1), GigaMath::grad($this->permutations[$ba + 1], $dx - 1, 0, $dz - 1));
                $noise[$index++] += GigaMath::lerp($fz, $x1, $x2) * $amplitude;
            }
        }
    }

    private function get3dNoise(array &$noise, float $x, float $y, float $z, int $sizeX, int $sizeY, int $sizeZ, float $scaleX, float $scaleY, float $scaleZ, float $amplitude): void {
        $n = -1;
        $x1 = $x2 = $x3 = $x4 = 0;
        $index = 0;
        for ($i = 0; $i < $sizeX; $i++) {
            $dx = $x + $this->offsetX + ($i * $scaleX);
            $floorX = GigaMath::floor($dx);
            $ix = $floorX & 255;
            $dx -= $floorX;
            $fx = GigaMath::fade($dx);
            for ($j = 0; $j < $sizeZ; $j++) {
                $dz = $z + $this->offsetZ + $j * $scaleZ;
                $floorZ = GigaMath::floor($dz);
                $iz = $floorZ & 255;
                $dz -= $floorZ;
                $fz = GigaMath::fade($dz);
                for ($k = 0; $k < $sizeY; $k++) {
                    $dy = $y + $this->offsetY + $k * $scaleY;
                    $floorY = GigaMath::floor($dy);
                    $iy = $floorY & 255;
                    $dy -= $floorY;
                    $fy = GigaMath::fade($dy);
                    if ($k === 0 || $iy !== $n) {
                        $n = $iy;
                        // Hash coordinates of the cube corners
                        $a = $this->permutations[$ix] + $iy;
                        $aa = $this->permutations[$a] + $iz;
                        $ab = $this->permutations[$a + 1] + $iz;
                        $b = $this->permutations[$ix + 1] + $iy;
                        $ba = $this->permutations[$b] + $iz;
                        $bb = $this->permutations[$b + 1] + $iz;
                        $x1 = GigaMath::lerp($fx, GigaMath::grad($this->permutations[$aa], $dx, $dy, $dz), GigaMath::grad($this->permutations[$ba], $dx - 1, $dy, $dz));
                        $x2 = GigaMath::lerp($fx, GigaMath::grad($this->permutations[$ab], $dx, $dy - 1, $dz), GigaMath::grad($this->permutations[$bb], $dx - 1, $dy - 1, $dz));
                        $x3 = GigaMath::lerp($fx, GigaMath::grad($this->permutations[$aa + 1], $dx, $dy, $dz - 1), GigaMath::grad($this->permutations[$ba + 1], $dx - 1, $dy, $dz - 1));
                        $x4 = GigaMath::lerp($fx, GigaMath::grad($this->permutations[$ab + 1], $dx, $dy - 1, $dz - 1), GigaMath::grad($this->permutations[$bb + 1], $dx - 1, $dy - 1, $dz - 1));
                    }

                    $y1 = GigaMath::lerp($fy, $x1, $x2);
                    $y2 = GigaMath::lerp($fy, $x3, $x4);

                    $noise[$index++] += GigaMath::lerp($fz, $y1, $y2) * $amplitude;
                }
            }
        }
    }
}
