<?php

namespace JonasWindmann\Giganilla\generator\biomegrid;

class WhittakerMapLayer extends MapLayer {
    private WhittakerClimateType $type;
    private MapLayer $belowLayer;
    private array $climateArray;

    public function __construct(int $seed, MapLayer $belowLayer, WhittakerClimateType $type) {
        parent::__construct($seed);
        $this->belowLayer = $belowLayer;
        $this->type = $type;
        $this->climateArray = [
            ['value' => 2, 'finalValue' => 4, 'crossTypes' => [3, 1]],
            ['value' => 3, 'finalValue' => 1, 'crossTypes' => [2, 4]]
        ];
    }

    public function generateValues(int $x, int $z, int $sizeX, int $sizeZ): array {
        if ($this->type === WhittakerClimateType::WARM_WET || $this->type === WhittakerClimateType::COLD_DRY) {
            return $this->swapValues($x, $z, $sizeX, $sizeZ);
        }

        return $this->modifyValues($x, $z, $sizeX, $sizeZ);
    }

    private function swapValues(int $x, int $z, int $sizeX, int $sizeZ): array {
        $gridX = $x - 1;
        $gridZ = $z - 1;
        $gridSizeX = $sizeX + 2;
        $gridSizeZ = $sizeZ + 2;

        $values = $this->belowLayer->generateValues($gridX, $gridZ, $gridSizeX, $gridSizeZ);

        $finalValues = [];
        $climate = $this->climateArray[$this->type->value];
        for ($i = 0; $i < $sizeZ; $i++) {
            for ($j = 0; $j < $sizeX; $j++) {
                $centerVal = $values[$j + 1 + ($i + 1) * $gridSizeX];
                if ($centerVal == $climate['value']) {
                    $upperVal = $values[$j + 1 + $i * $gridSizeX];
                    $lowerVal = $values[$j + 1 + ($i + 2) * $gridSizeX];
                    $leftVal = $values[$j + ($i + 1) * $gridSizeX];
                    $rightVal = $values[$j + 2 + ($i + 1) * $gridSizeX];

                    foreach ((array)$climate['crossTypes'] as $type) {
                        if ($upperVal == $type || $lowerVal == $type || $leftVal == $type || $rightVal == $type) {
                            $centerVal = $climate['finalValue'];
                            break;
                        }
                    }
                }

                $finalValues[$j + $i * $sizeX] = $centerVal;
            }
        }
        return $finalValues;
    }

    private function modifyValues(int $x, int $z, int $sizeX, int $sizeZ): array {
        $values = $this->belowLayer->generateValues($x, $z, $sizeX, $sizeZ);
        $finalValues = [];
        for ($i = 0; $i < $sizeZ; $i++) {
            for ($j = 0; $j < $sizeX; $j++) {
                $val = $values[$j + $i * $sizeX];
                if ($val != 0) {
                    $this->setCoordsSeed($x + $j, $z + $i);
                    if ($this->nextInt(13) == 0) {
                        $val += 1000;
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