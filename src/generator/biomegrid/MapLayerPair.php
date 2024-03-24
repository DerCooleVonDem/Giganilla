<?php

namespace JonasWindmann\Giganilla\generator\biomegrid;

class MapLayerPair {
    public MapLayer $highResolution;
    public MapLayer $lowResolution;

    public function __construct(MapLayer $highResolution, MapLayer $lowResolution) {
        $this->highResolution = $highResolution;
        $this->lowResolution = $lowResolution;
    }
}