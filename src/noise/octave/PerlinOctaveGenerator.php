<?php

namespace JonasWindmann\Giganilla\noise\octave;

use JonasWindmann\Giganilla\GigaMath;
use JonasWindmann\Giganilla\GigaRandom;
use JonasWindmann\Giganilla\noise\PerlinNoise;

class PerlinOctaveGenerator extends ScalableOctaves {
    private array $perlinOctaves = [];
    protected float $sizeX;
    protected float $sizeY;
    private float $sizeZ;

    public function __construct(GigaRandom $random, int $octavesNum, float $sizeXv, float $sizeYv, float $sizeZv) {
        for ($i = 0; $i < $octavesNum; ++$i) {
            $perlin = new PerlinNoise($random);
            $this->perlinOctaves[] = $perlin;
        }

        $this->sizeX = $sizeXv;
        $this->sizeY = $sizeYv;
        $this->sizeZ = $sizeZv;
        // Assuming xScale, yScale, and zScale are defined elsewhere in your class
    }

    public function GetFractalBrownianMotion(float $x, float $y, float $z, float $lacunarity, float $persistence): array
    {
        $noise = array_fill(0, $this->GetArraySize(), 0.0);

        $freq = 1;
        $amp = 1;

        $x *= $this->getXScale();
        $y *= $this->getYScale();
        $z *= $this->getZScale();

        foreach ($this->perlinOctaves as $octave) {
            $dx = $x * $freq;
            $dz = $z * $freq;
            // compute integer part
            $lx = GigaMath::floorLong($dx);
            $lz = GigaMath::floorLong($dz);
            // compute fractional part
            $dx -= $lx;
            $dz -= $lz;
            // wrap integer part to 0..16777216
            $lx %= 16777216;
            $lz %= 16777216;
            // add to fractional part
            $dx += $lx;
            $dz += $lz;

            $dy = $y * $freq;

            $octave->GetNoise($noise, $dx, $dy, $dz, $this->sizeX, $this->sizeY, $this->sizeZ, $this->getXScale() * $freq, $this->getYScale() * $freq, $this->getZScale() * $freq, $amp);

            $freq *= $lacunarity;
            $amp *= $persistence;
        }

        return $noise;
    }

    public function GetArraySize(): float|int
    {
        return $this->sizeX * $this->sizeY * $this->sizeZ;
    }
}