<?php

namespace JonasWindmann\Giganilla\generator\biomegrid;

use JonasWindmann\Giganilla\biome\BiomeList;

class DeepOceanMapLayer extends MapLayer {
    private MapLayer $belowLayer;

    public function __construct(int $seed, MapLayer $belowLayer) {
        parent::__construct($seed);
        $this->belowLayer = $belowLayer;
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
                $centerVal = $values[$j + 1 + ($i + 1) * $gridSizeX];
                if ($centerVal == 0) {
                    $upperVal = $values[$j + 1 + $i * $gridSizeX];
                    $lowerVal = $values[$j + 1 + ($i + 2) * $gridSizeX];
                    $leftVal = $values[$j + ($i + 1) * $gridSizeX];
                    $rightVal = $values[$j + 2 + ($i + 1) * $gridSizeX];
                    if ($upperVal == 0 && $lowerVal == 0 && $leftVal == 0 && $rightVal == 0) {
                        $this->setCoordsSeed($x + $j, $z + $i);
                        $finalValues[$j + $i * $sizeX] = $this->nextInt(100) == 0 ? BiomeList::MUSHROOM_ISLAND : BiomeList::DEEP_OCEAN;
                    } else {
                        $finalValues[$j + $i * $sizeX] = $centerVal;
                    }
                } else {
                    $finalValues[$j + $i * $sizeX] = $centerVal;
                }
            }
        }

        return $finalValues;
    }

    public function __destruct() {
        unset($this->belowLayer);
    }
}