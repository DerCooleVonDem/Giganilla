<?php

namespace JonasWindmann\Giganilla\generator\biomegrid;

use JonasWindmann\Giganilla\noise\octave\BukkitSimplexOctaveGenerator;

class NoiseMapLayer extends MapLayer {
    private BukkitSimplexOctaveGenerator $noiseGen;

    public function __construct(int $seed) {
        parent::__construct($seed);
        $this->noiseGen = new BukkitSimplexOctaveGenerator($this->random, 2);
    }

    public function generateValues(int $x, int $z, int $sizeX, int $sizeZ): array {
        $values = [];
        for ($i = 0; $i < $sizeZ; ++$i) {
            for ($j = 0; $j < $sizeX; ++$j) {
                $noise = $this->noiseGen->noise($x + $j, $z + $i, 0.175, 0.8, true) * 4.0;
                $val = 0;
                if ($noise >= 0.05) {
                    $val = $noise <= 0.2 ? 3 : 2;
                } else {
                    $this->setCoordsSeed($x + $j, $z + $i);
                    $val = $this->nextInt(2) == 0 ? 3 : 0;
                }
                $values[$j + $i * $sizeX] = $val;
            }
        }
        return $values;
    }
}