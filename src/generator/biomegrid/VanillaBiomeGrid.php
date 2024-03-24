<?php

namespace JonasWindmann\Giganilla\generator\biomegrid;

class VanillaBiomeGrid {
    private array $biomes;

    public function __construct(array $biome) {
        $this->biomes = $biome;
    }

    public function getBiome(int $x, int $z): int {
        $key = $x | ($z << 4);
        if (!array_key_exists($key, $this->biomes)) {
            return -1;
        }

        return $this->biomes[$key] & 0xFF;
    }

    public function setBiome(int $x, int $z, int $biomeId): void {
        $this->biomes[$x | ($z << 4)] = $biomeId;
    }

    public function setBiomes(array $grid): void {
        $this->biomes = $grid;
    }
}
