<?php

namespace JonasWindmann\Giganilla\generator\biomegrid;

use JonasWindmann\Giganilla\biome\BiomeList;

class ShoreMapLayer extends MapLayer {

    private MapLayer $belowLayer;
    private bool $isUHC;
    private array $OCEANS = [BiomeList::OCEAN, BiomeList::DEEP_OCEAN];

    private array $SPECIAL_SHORES = [
        BiomeList::EXTREME_HILLS => BiomeList::STONE_BEACH,
        BiomeList::EXTREME_HILLS_PLUS => BiomeList::STONE_BEACH,
        BiomeList::EXTREME_HILLS_MOUNTAINS => BiomeList::STONE_BEACH,
        BiomeList::EXTREME_HILLS_PLUS_MOUNTAINS => BiomeList::STONE_BEACH,
        BiomeList::ICE_PLAINS => BiomeList::COLD_BEACH,
        BiomeList::ICE_MOUNTAINS => BiomeList::COLD_BEACH,
        BiomeList::ICE_PLAINS_SPIKES => BiomeList::COLD_BEACH,
        BiomeList::COLD_TAIGA => BiomeList::COLD_BEACH,
        BiomeList::COLD_TAIGA_HILLS => BiomeList::COLD_BEACH,
        BiomeList::COLD_TAIGA_MOUNTAINS => BiomeList::COLD_BEACH,
        BiomeList::MUSHROOM_ISLAND => BiomeList::MUSHROOM_SHORE,
        BiomeList::SWAMPLAND => BiomeList::SWAMPLAND,
        BiomeList::MESA => BiomeList::MESA,
        BiomeList::MESA_PLATEAU_FOREST => BiomeList::MESA_PLATEAU_FOREST,
        BiomeList::MESA_PLATEAU_FOREST_MOUNTAINS => BiomeList::MESA_PLATEAU_FOREST_MOUNTAINS,
        BiomeList::MESA_PLATEAU => BiomeList::MESA_PLATEAU,
        BiomeList::MESA_PLATEAU_MOUNTAINS => BiomeList::MESA_PLATEAU_MOUNTAINS,
        BiomeList::MESA_BRYCE => BiomeList::MESA_BRYCE
    ];

    public function __construct(int $seed, MapLayer $belowLayer, bool $isUHC = false) {
        parent::__construct($seed);
        $this->belowLayer = $belowLayer;
        $this->isUHC = $isUHC;
    }

    public function generateValues(int $x, int $z, int $sizeX, int $sizeZ): array {
        $gridX = $x - 1;
        $gridZ = $z - 1;
        $gridSizeX = $sizeX + 2;
        $gridSizeZ = $sizeZ + 2;
        $values = $this->belowLayer->generateValues($gridX, $gridZ, $gridSizeX, $gridSizeZ);

        $finalValues = [];
        for ($i = 0; $i < $sizeZ; $i++) {
            for ($j = 0; $j < $sizeX; $j++) {
                if ($this->isUHC) {
                    $centerVal = $values[$j + 1 + ($i + 1) * $gridSizeX];
                    $finalValues[$j + $i * $sizeX] = $centerVal;
                } else {
                    $upperVal = $values[$j + 1 + $i * $gridSizeX];
                    $lowerVal = $values[$j + 1 + ($i + 2) * $gridSizeX];
                    $leftVal = $values[$j + ($i + 1) * $gridSizeX];
                    $rightVal = $values[$j + 2 + ($i + 1) * $gridSizeX];
                    $centerVal = $values[$j + 1 + ($i + 1) * $gridSizeX];
                    if (!$this->oceanContains($centerVal) && (
                            $this->oceanContains($upperVal) || $this->oceanContains($lowerVal)
                            || $this->oceanContains($leftVal) || $this->oceanContains($rightVal))) {
                        $finalValues[$j + $i * $sizeX] = array_key_exists($centerVal, $this->SPECIAL_SHORES)
                            ? $this->SPECIAL_SHORES[$centerVal] : BiomeList::BEACH;
                    } else {
                        $finalValues[$j + $i * $sizeX] = $centerVal;
                    }
                }
            }
        }
        return $finalValues;
    }

    private function oceanContains(int $value): bool {
        return in_array($value, $this->OCEANS);
    }

    public function __destruct() {
        unset($this->belowLayer);
    }
}
