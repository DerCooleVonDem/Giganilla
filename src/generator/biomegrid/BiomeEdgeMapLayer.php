<?php

namespace JonasWindmann\Giganilla\generator\biomegrid;

use JonasWindmann\Giganilla\biome\BiomeList;

class BiomeEdgeMapLayer extends MapLayer {
    private MapLayer $belowLayer;
    private array $EDGES;

    public function __construct(int $seed, MapLayer $belowLayer, bool $isUHC = false) {
        parent::__construct($seed);
        $this->belowLayer = $belowLayer;
        if ($isUHC) {
            $this->EDGES = [
                [
                    [BiomeList::MESA_PLATEAU_FOREST => BiomeList::MESA, BiomeList::MESA_PLATEAU => BiomeList::MESA],
                    []
                ],
                [
                    [BiomeList::DESERT => BiomeList::EXTREME_HILLS_PLUS],
                    [BiomeList::ICE_PLAINS]
                ]
            ];
        } else {
            $this->EDGES = [
                [
                    [BiomeList::MEGA_TAIGA => BiomeList::TAIGA],
                    []
                ],
                [
                    [BiomeList::DESERT => BiomeList::EXTREME_HILLS_PLUS],
                    [BiomeList::ICE_PLAINS]
                ],
                [
                    [BiomeList::SWAMPLAND => BiomeList::PLAINS],
                    [BiomeList::DESERT, BiomeList::COLD_TAIGA, BiomeList::ICE_PLAINS]
                ],
                [
                    [BiomeList::SWAMPLAND => BiomeList::JUNGLE_EDGE],
                    [BiomeList::JUNGLE]
                ]
            ];
        }
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
                $val = $centerVal;
                foreach ($this->EDGES as $entry) {
                    $map = $entry[0];
                    if (array_key_exists($centerVal, $map)) {
                        $upperVal = $values[$j + 1 + $i * $gridSizeX];
                        $lowerVal = $values[$j + 1 + ($i + 2) * $gridSizeX];
                        $leftVal = $values[$j + ($i + 1) * $gridSizeX];
                        $rightVal = $values[$j + 2 + ($i + 1) * $gridSizeX];
                        if (empty($entry[1]) && (
                                !array_key_exists($upperVal, $map) ||
                                !array_key_exists($lowerVal, $map) ||
                                !array_key_exists($leftVal, $map) ||
                                !array_key_exists($rightVal, $map))) {
                            $val = $map[$centerVal];
                            break;
                        } elseif (!empty($entry[1]) && (
                                in_array($upperVal, $entry[1]) ||
                                in_array($lowerVal, $entry[1]) ||
                                in_array($leftVal, $entry[1]) ||
                                in_array($rightVal, $entry[1]))) {
                            $val = $map[$centerVal];
                            break;
                        }
                    }
                }
                $finalValues[$j + $i * $sizeX] = $val;
            }
        }

        return $finalValues;
    }

    public function __destruct() {
        unset($this->belowLayer);
    }
}
