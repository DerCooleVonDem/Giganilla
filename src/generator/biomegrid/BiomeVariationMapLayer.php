<?php

namespace JonasWindmann\Giganilla\generator\biomegrid;

use JonasWindmann\Giganilla\biome\BiomeList;

class BiomeVariationMapLayer extends MapLayer {
    private MapLayer $belowLayer;
    private ?MapLayer $variationLayer;
    private bool $isUHC;
    private array $islands = [BiomeList::PLAINS, BiomeList::FOREST];
    private array $variations = [
        BiomeList::DESERT => [BiomeList::DESERT_HILLS],
        BiomeList::FOREST => [BiomeList::FOREST_HILLS],
        BiomeList::BIRCH_FOREST => [BiomeList::BIRCH_FOREST_HILLS],
        BiomeList::ROOFED_FOREST => [BiomeList::PLAINS],
        BiomeList::TAIGA => [BiomeList::TAIGA_HILLS],
        BiomeList::MEGA_TAIGA => [BiomeList::MEGA_TAIGA_HILLS],
        BiomeList::COLD_TAIGA => [BiomeList::COLD_TAIGA_HILLS],
        BiomeList::PLAINS => [BiomeList::FOREST, BiomeList::FOREST, BiomeList::FOREST_HILLS],
        BiomeList::ICE_PLAINS => [BiomeList::ICE_MOUNTAINS],
        BiomeList::JUNGLE => [BiomeList::JUNGLE_HILLS],
        BiomeList::OCEAN => [BiomeList::DEEP_OCEAN],
        BiomeList::EXTREME_HILLS => [BiomeList::EXTREME_HILLS_PLUS],
        BiomeList::SAVANNA => [BiomeList::SAVANNA_PLATEAU],
        BiomeList::MESA_PLATEAU_FOREST => [BiomeList::MESA],
        BiomeList::MESA_PLATEAU => [BiomeList::MESA],
        BiomeList::MESA => [BiomeList::MESA]
    ];

    public function __construct(int $seed, MapLayer $belowLayer, ?MapLayer $variationLayer = null, bool $isUHC = false) {
        parent::__construct($seed);
        $this->belowLayer = $belowLayer;
        $this->variationLayer = $variationLayer;
        $this->isUHC = $isUHC;
        if ($isUHC) {
            $this->variations = [
                BiomeList::DESERT => [BiomeList::DESERT_HILLS],
                BiomeList::FOREST => [BiomeList::FOREST_HILLS],
                BiomeList::BIRCH_FOREST => [BiomeList::BIRCH_FOREST_HILLS],
                BiomeList::ROOFED_FOREST => [BiomeList::PLAINS],
                BiomeList::TAIGA => [BiomeList::TAIGA_HILLS],
                BiomeList::COLD_TAIGA => [BiomeList::COLD_TAIGA_HILLS],
                BiomeList::PLAINS => [BiomeList::FOREST, BiomeList::FOREST, BiomeList::FOREST_HILLS],
                BiomeList::ICE_PLAINS => [BiomeList::ICE_MOUNTAINS],
                BiomeList::EXTREME_HILLS => [BiomeList::EXTREME_HILLS_PLUS],
                BiomeList::SAVANNA => [BiomeList::SAVANNA_PLATEAU],
                BiomeList::MESA_PLATEAU_FOREST => [BiomeList::MESA],
                BiomeList::MESA_PLATEAU => [BiomeList::MESA],
                BiomeList::MESA => [BiomeList::MESA]
            ];
        }
    }

    public function generateValues(int $x, int $z, int $sizeX, int $sizeZ): array {
        if ($this->variationLayer === null) {
            return $this->generateRandomValues($x, $z, $sizeX, $sizeZ);
        }

        return $this->mergeValues($x, $z, $sizeX, $sizeZ);
    }

    private function generateRandomValues(int $x, int $z, int $sizeX, int $sizeZ): array {
        $values = $this->belowLayer->generateValues($x, $z, $sizeX, $sizeZ);

        $finalValues = [];
        for ($i = 0; $i < $sizeZ; $i++) {
            for ($j = 0; $j < $sizeX; $j++) {
                $val = $values[$j + $i * $sizeX];
                if ($val > 0) {
                    $this->setCoordsSeed($x + $j, $z + $i);
                    $val = $this->nextInt(30) + 2;
                }
                $finalValues[$j + $i * $sizeX] = $val;
            }
        }
        return $finalValues;
    }

    private function mergeValues(int $x, int $z, int $sizeX, int $sizeZ): array {
        $gridX = $x - 1;
        $gridZ = $z - 1;
        $gridSizeX = $sizeX + 2;
        $gridSizeZ = $sizeZ + 2;

        $values = $this->belowLayer->generateValues($gridX, $gridZ, $gridSizeX, $gridSizeZ);
        $variationValues = $this->variationLayer->generateValues($gridX, $gridZ, $gridSizeX, $gridSizeZ);

        $finalValues = [];
        for ($i = 0; $i < $sizeZ; $i++) {
            for ($j = 0; $j < $sizeX; $j++) {
                $this->setCoordsSeed($x + $j, $z + $i);
                $centerValue = $values[$j + 1 + ($i + 1) * $gridSizeX];
                $variationValue = $variationValues[$j + 1 + ($i + 1) * $gridSizeX];
                if ($centerValue != 0 && $variationValue == 3 && $centerValue < 128) {
                    $finalValues[$j + $i * $sizeX] = in_array($centerValue + 128, BiomeList::$ALL_BIOMES) ? $centerValue + 128 : $centerValue;
                } elseif ($variationValue == 2 || $this->nextInt(3) == 0) {
                    $val = $centerValue;
                    if (array_key_exists($centerValue, $this->variations)) {
                        $var = $this->variations[$centerValue];
                        $val = $var[$this->nextInt(count($var))];
                    } elseif (!$this->isUHC && $centerValue == BiomeList::DEEP_OCEAN && $this->nextInt(3) == 0) {
                        $val = $this->islands[$this->nextInt(count($this->islands))];
                    }

                    if ($variationValue == 2 && $val != $centerValue) {
                        $val = in_array($val + 128, BiomeList::$ALL_BIOMES) ? $val + 128 : $centerValue;
                    }

                    $count = 0;
                    if ($values[$j + 1 + $i * $gridSizeX] == $centerValue) $count++; // upper value
                    if ($values[$j + 1 + ($i + 2) * $gridSizeX] == $centerValue) $count++; // lower value
                    if ($values[$j + ($i + 1) * $gridSizeX] == $centerValue) $count++; // left value
                    if ($values[$j + 2 + ($i + 1) * $gridSizeX] == $centerValue) $count++; // right value
                    $finalValues[$j + $i * $sizeX] = $count < 3 ? $centerValue : $val;
                } else {
                    $finalValues[$j + $i * $sizeX] = $centerValue;
                }
            }
        }
        return $finalValues;
    }

    public function __destruct() {
        unset($this->belowLayer, $this->variationLayer);
    }
}
