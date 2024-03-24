<?php

namespace JonasWindmann\Giganilla\generator\biomegrid;

use JonasWindmann\Giganilla\biome\BiomeList;

class BiomeThinEdgeMapLayer extends MapLayer {
    private MapLayer $belowLayer;
    private bool $isUHC;
    private array $OCEANS = [BiomeList::OCEAN, BiomeList::DEEP_OCEAN];
    private array $MESA_EDGES = [
        BiomeList::MESA => BiomeList::DESERT,
        BiomeList::MESA_BRYCE => BiomeList::DESERT,
        BiomeList::MESA_PLATEAU_FOREST => BiomeList::DESERT,
        BiomeList::MESA_PLATEAU_FOREST_MOUNTAINS => BiomeList::DESERT,
        BiomeList::MESA_PLATEAU => BiomeList::DESERT,
        BiomeList::MESA_PLATEAU_MOUNTAINS => BiomeList::DESERT
    ];
    private array $JUNGLE_EDGES = [
        BiomeList::JUNGLE => BiomeList::JUNGLE_EDGE,
        BiomeList::JUNGLE_HILLS => BiomeList::JUNGLE_EDGE,
        BiomeList::JUNGLE_MOUNTAINS => BiomeList::JUNGLE_EDGE,
        BiomeList::JUNGLE_EDGE_MOUNTAINS => BiomeList::JUNGLE_EDGE
    ];

    private array $EDGES;

    public function __construct(int $seed, MapLayer $belowLayer, bool $isUHC = false) {
        $this->EDGES = [
            [$this->MESA_EDGES, []],
            [$this->JUNGLE_EDGES, [BiomeList::JUNGLE, BiomeList::JUNGLE_HILLS, BiomeList::JUNGLE_MOUNTAINS, BiomeList::JUNGLE_EDGE_MOUNTAINS, BiomeList::FOREST, BiomeList::TAIGA]]
        ];

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
                $centerVal = $values[$j + 1 + ($i + 1) * $gridSizeX];
                $val = $centerVal;
                if (!$this->isUHC) {
                    foreach ($this->EDGES as $entry) {
                        $map = $entry[0];
                        if (array_key_exists($centerVal, $map)) {
                            $upperVal = $values[$j + 1 + $i * $gridSizeX];
                            $lowerVal = $values[$j + 1 + ($i + 2) * $gridSizeX];
                            $leftVal = $values[$j + ($i + 1) * $gridSizeX];
                            $rightVal = $values[$j + 2 + ($i + 1) * $gridSizeX];
                            if (empty($entry[1]) && (
                                    $this->oceanContains($upperVal) && !array_key_exists($upperVal, $map) ||
                                    $this->oceanContains($lowerVal) && !array_key_exists($lowerVal, $map) ||
                                    $this->oceanContains($leftVal) && !array_key_exists($leftVal, $map) ||
                                    $this->oceanContains($rightVal) && !array_key_exists($rightVal, $map))) {
                                $val = $map[$centerVal];
                                break;
                            } elseif (!empty($entry[1]) && (
                                    $this->oceanContains($upperVal) && in_array($upperVal, $entry[1]) ||
                                    $this->oceanContains($lowerVal) && in_array($lowerVal, $entry[1]) ||
                                    $this->oceanContains($leftVal) && in_array($leftVal, $entry[1]) ||
                                    $this->oceanContains($rightVal) && in_array($rightVal, $entry[1]))) {
                                $val = $map[$centerVal];
                                break;
                            }
                        }
                    }
                }
                $finalValues[$j + $i * $sizeX] = $val;
            }
        }
        return $finalValues;
    }

    private function oceanContains(int $value): bool {
        return !in_array($value, $this->OCEANS);
    }

    private function edgesContains(array $entry, int $value): bool {
        return !in_array($value, $entry);
    }

    public function __destruct() {
        unset($this->belowLayer);
    }
}
