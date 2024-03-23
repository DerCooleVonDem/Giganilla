<?php

namespace JonasWindmann\Giganilla\noise\octave;

use JonasWindmann\Giganilla\GigaRandom;
use JonasWindmann\Giganilla\noise\SimplexNoise;

class SimplexOctaveGenerator extends ScalableOctaves {
    private array $simplexOctaves = [];
    private float $sizeX;
    private float $sizeY;
    private float $sizeZ;

    public function __construct(GigaRandom $random, int $octavesNum, float $sizeXv, float $sizeYv, float $sizeZv) {
        for ($i = 0; $i < $octavesNum; ++$i) {
            $simplex = new SimplexNoise($random);
            $this->simplexOctaves[] = $simplex;
        }

        $this->sizeX = $sizeXv;
        $this->sizeY = $sizeYv;
        $this->sizeZ = $sizeZv;
    }

    public function GetFractalBrownianMotion(float $x, float $y, float $z, float $lacunarity, float $persistence): array
    {
        $noise = array_fill(0, $this->GetArraySize(), 0.0);

        $freq = 1.0;
        $amp = 1.0;

        foreach ($this->simplexOctaves as $octave) {
            $octave->GetNoise($noise, $x, $y, $z, $this->sizeX, $this->sizeY, $this->sizeZ, $this->getXScale() * $freq, $this->getYScale() * $freq, $this->getZScale() * $freq, 0.55 / $amp);
            $freq *= $lacunarity;
            $amp *= $persistence;
        }

        return $noise;
    }

    public function GetArraySize(): int
    {
        // TODO: Why could this return a float? -> PhpStan error
        return (int)($this->sizeX * $this->sizeY * $this->sizeZ);
    }

    public function Noise(float $x, float $y, float $z, float $frequency, float $amplitude, bool $normalized): float|int
    {
        $result = 0.0;
        $amp = 1.0;
        $freq = 1.0;
        $max = 0.0;

        $x *= $this->GetXScale();
        $y *= $this->GetYScale();
        $z *= $this->GetZScale();

        foreach ($this->simplexOctaves as $octave) {
            $value = $octave->Noise3d($x * $freq, $y * $freq, $z * $freq) * $amp;

            $result += $value;
            $max += $amp;
            $freq *= $frequency;
            $amp *= $amplitude;
        }

        if ($normalized) {
            $result /= $max;
        }

        return $result;
    }

    /**
     * @return float
     */
    public function getSizeX(): float
    {
        return $this->sizeX;
    }

    /**
     * @return float
     */
    public function getSizeY(): float
    {
        return $this->sizeY;
    }

    /**
     * @return float
     */
    public function getSizeZ(): float
    {
        return $this->sizeZ;
    }
}
