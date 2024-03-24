<?php

namespace JonasWindmann\Giganilla\generator\biomegrid;

class ErosionMapLayer extends MapLayer {
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
                $upperLeftVal = $values[$j + $i * $gridSizeX];
                $lowerLeftVal = $values[$j + ($i + 2) * $gridSizeX];
                $upperRightVal = $values[$j + 2 + $i * $gridSizeX];
                $lowerRightVal = $values[$j + 2 + ($i + 2) * $gridSizeX];
                $centerVal = $values[$j + 1 + ($i + 1) * $gridSizeX];

                $this->setCoordsSeed($x + $j, $z + $i);
                if ($centerVal != 0 && ($upperLeftVal == 0 || $upperRightVal == 0 || $lowerLeftVal == 0 || $lowerRightVal == 0)) {
                    $finalValues[$j + $i * $sizeX] = $this->nextInt(5) == 0 ? 0 : $centerVal;
                } elseif ($centerVal == 0 && ($upperLeftVal != 0 || $upperRightVal != 0 || $lowerLeftVal != 0 || $lowerRightVal != 0)) {
                    if ($this->nextInt(3) == 0) {
                        $finalValues[$j + $i * $sizeX] = $upperLeftVal;
                    } else {
                        $finalValues[$j + $i * $sizeX] = 0;
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
