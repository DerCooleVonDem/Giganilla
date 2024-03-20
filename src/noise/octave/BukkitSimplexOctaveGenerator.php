<?php

namespace JonasWindmann\Giganilla\noise\octave;


use JonasWindmann\Giganilla\GigaRandom;
use JonasWindmann\Giganilla\noise\BukkitSimplexNoiseGenerator;

class BukkitSimplexOctaveGenerator {
    private array $simplexOctaves = [];

    public function __construct(GigaRandom $random, int $octaves) {
        for ($i = 0; $i < $octaves; $i++) {
            $simplexNoise = new BukkitSimplexNoiseGenerator($random);
            $this->simplexOctaves[] = $simplexNoise;
        }
    }

    public function Noise(float $x, float $y, float $frequency, float $amplitude, bool $normalized): float
    {
        $result = 0.0;
        $amp = 1.0;
        $freq = 1.0;
        $max = 0.0;

        foreach ($this->simplexOctaves as $octave) {
            $value = $octave->Simplex2d($x * $freq, $y * $freq) * $amp;

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
}