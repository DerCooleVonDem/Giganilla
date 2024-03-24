<?php

namespace JonasWindmann\Giganilla\generator\biomegrid;

class WhittakerClimate {
    public int $value;
    public int $finalValue;
    public array $crossTypes;

    public function __construct(int $value, int $finalValue, array $crossTypes) {
        $this->value = $value;
        $this->finalValue = $finalValue;
        $this->crossTypes = $crossTypes;
    }
}