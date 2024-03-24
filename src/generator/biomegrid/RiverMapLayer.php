<?php

namespace JonasWindmann\Giganilla\generator\biomegrid;

use JonasWindmann\Giganilla\biome\BiomeList;

class RiverMapLayer extends MapLayer {
    private MapLayer $belowLayer;
    private ?MapLayer $mergeLayer;
    private bool $isUHC;
    private array $OCEANS = [BiomeList::OCEAN, BiomeList::DEEP_OCEAN];
    private array $SPECIAL_RIVERS = [
        BiomeList::ICE_PLAINS => BiomeList::FROZEN_RIVER,
        BiomeList::MUSHROOM_ISLAND => BiomeList::MUSHROOM_SHORE,
        BiomeList::MUSHROOM_SHORE => BiomeList::MUSHROOM_SHORE
    ];
    private int $CLEAR_VALUE = 0;
    private int $RIVER_VALUE = 1;

    public function __construct(int $seed, MapLayer $belowLayer, ?MapLayer $mergeLayer = null, bool $isUHC = false) {
        parent::__construct($seed);
        $this->belowLayer = $belowLayer;
        $this->mergeLayer = $mergeLayer;
        $this->isUHC = $isUHC;
    }

    public function generateValues(int $x, int $z, int $sizeX, int $sizeZ): array {
        if ($this->mergeLayer === null) {
            return $this->generateRivers($x, $z, $sizeX, $sizeZ);
        }
        return $this->mergeRivers($x, $z, $sizeX, $sizeZ);
    }

    private function generateRivers(int $x, int $z, int $sizeX, int $sizeZ): array {
        $gridX = $x - 1;
        $gridZ = $z - 1;
        $gridSizeX = $sizeX + 2;
        $gridSizeZ = $sizeZ + 2;

        $values = $this->belowLayer->generateValues($gridX, $gridZ, $gridSizeX, $gridSizeZ);
        $finalValues = [];
        for ($i = 0; $i < $sizeZ; $i++) {
            for ($j = 0; $j < $sizeX; $j++) {
                $centerVal = $values[$j + 1 + ($i + 1) * $gridSizeX] & 1;
                $upperVal = $values[$j + 1 + $i * $gridSizeX] & 1;
                $lowerVal = $values[$j + 1 + ($i + 2) * $gridSizeX] & 1;
                $leftVal = $values[$j + ($i + 1) * $gridSizeX] & 1;
                $rightVal = $values[$j + 2 + ($i + 1) * $gridSizeX] & 1;
                $val = $this->CLEAR_VALUE;
                if ($centerVal !== $upperVal || $centerVal !== $lowerVal || $centerVal !== $leftVal || $centerVal !== $rightVal) {
                    $val = $this->RIVER_VALUE;
                }
                $finalValues[$j + $i * $sizeX] = $val;
            }
        }
        return $finalValues;
    }

    private function mergeRivers(int $x, int $z, int $sizeX, int $sizeZ): array {
        $values = $this->belowLayer->generateValues($x, $z, $sizeX, $sizeZ);
        $mergeValues = $this->mergeLayer->generateValues($x, $z, $sizeX, $sizeZ);

        $finalValues = [];
        for ($i = 0; $i < $sizeX * $sizeZ; $i++) {
            $val = $mergeValues[$i];
            if (!$this->isUHC && in_array($mergeValues[$i], $this->OCEANS)) {
                $val = $mergeValues[$i];
            } elseif ($values[$i] === $this->RIVER_VALUE) {
                if (array_key_exists($mergeValues[$i], $this->SPECIAL_RIVERS)) {
                    $val = $this->SPECIAL_RIVERS[$mergeValues[$i]];
                } else {
                    $val = BiomeList::RIVER;
                }
            }

            $finalValues[$i] = $val;
        }

        return $finalValues;
    }

    public function __destruct() {
        unset($this->belowLayer, $this->mergeLayer);
    }
}
