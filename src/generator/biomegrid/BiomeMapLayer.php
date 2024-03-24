<?php

namespace JonasWindmann\Giganilla\generator\biomegrid;

use JonasWindmann\Giganilla\biome\BiomeList;

class BiomeMapLayer extends MapLayer {

    private MapLayer $belowLayer;
    private bool $isUHC;
    private array $WET = [BiomeList::PLAINS, BiomeList::PLAINS, BiomeList::FOREST, BiomeList::BIRCH_FOREST, BiomeList::ROOFED_FOREST, BiomeList::EXTREME_HILLS];
    private array $WARM = [BiomeList::DESERT, BiomeList::DESERT, BiomeList::DESERT, BiomeList::SAVANNA, BiomeList::SAVANNA, BiomeList::PLAINS];
    private array $DRY = [BiomeList::PLAINS, BiomeList::FOREST, BiomeList::TAIGA, BiomeList::EXTREME_HILLS];
    private array $COLD = [BiomeList::ICE_PLAINS, BiomeList::ICE_PLAINS, BiomeList::COLD_TAIGA];
    private array $WARM_LARGE = [BiomeList::MESA_PLATEAU_FOREST, BiomeList::MESA_PLATEAU_FOREST, BiomeList::MESA_PLATEAU];
    private array $DRY_LARGE = [BiomeList::MEGA_TAIGA];
    private array $WET_LARGE = [BiomeList::JUNGLE];

    public function __construct(int $seed, MapLayer $belowLayer, bool $isUHC = false) {
        parent::__construct($seed);
        $this->belowLayer = $belowLayer;
        $this->isUHC = $isUHC;
        if ($isUHC) {
            $this->WET = [BiomeList::PLAINS, BiomeList::PLAINS, BiomeList::FOREST, BiomeList::BIRCH_FOREST, BiomeList::ROOFED_FOREST, BiomeList::EXTREME_HILLS];
        }
    }

    public function generateValues(int $x, int $z, int $sizeX, int $sizeZ): array {
        $values = $this->belowLayer->generateValues($x, $z, $sizeX, $sizeZ);

        $finalValues = [];
        for ($i = 0; $i < $sizeZ; $i++) {
            for ($j = 0; $j < $sizeX; $j++) {
                $val = $values[$j + $i * $sizeX];
                if ($val != 0) {
                    $this->setCoordsSeed($x + $j, $z + $i);
                    switch ($val) {
                        case 1:
                            $val = $this->DRY[$this->nextInt(count($this->DRY))];
                            break;
                        case 2:
                            $val = $this->WARM[$this->nextInt(count($this->WARM))];
                            break;
                        case 3:
                        case 1003:
                            $val = $this->COLD[$this->nextInt(count($this->COLD))];
                            break;
                        case 4:
                            $val = $this->WET[$this->nextInt(count($this->WET))];
                            break;
                        case 1001:
                            $val = $this->isUHC ? $this->WET[$this->nextInt(count($this->WET))] : $this->DRY_LARGE[$this->nextInt(count($this->DRY_LARGE))];
                            break;
                        case 1002:
                            $val = $this->WARM_LARGE[$this->nextInt(count($this->WARM_LARGE))];
                            break;
                        case 1004:
                            $val = $this->isUHC ? $this->WARM_LARGE[$this->nextInt(count($this->WARM_LARGE))] : $this->WET_LARGE[$this->nextInt(count($this->WET_LARGE))];
                            break;
                        default:
                            break;
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
