<?php

namespace JonasWindmann\Giganilla\generator\biomegrid;

use JonasWindmann\Giganilla\GigaRandom;

class MapLayer {
    protected int $seed;
    protected GigaRandom $random;

    public function __construct(int $seed) {
        $this->seed = $seed;
        $this->random = new GigaRandom($seed);
    }

    public function nextInt(int $max): int {
        return $this->random->nextIntWithBound($max);
    }

    public function setCoordsSeed(int $x, int $z): void {
        $this->random->setSeed($this->seed);
        $this->random->setSeed($x * $this->random->nextInt() + $z * $this->random->nextInt() ^ $this->seed);
    }

    public function generateValues(int $x, int $z, int $sizeX, int $sizeZ): array {
        // Implement in subclasses
        return [];
    }

    public static function initialize(int $seed, bool $isUHC): MapLayerPair {
        $zoom = 2;

        $layer = new NoiseMapLayer($seed);

        $layer = new WhittakerMapLayer($seed + 1, $layer, WhittakerClimateType::WARM_WET);
        $layer = new WhittakerMapLayer($seed + 1, $layer, WhittakerClimateType::COLD_DRY);
        $layer = new WhittakerMapLayer($seed + 1, $layer, WhittakerClimateType::LARGER_BIOMES);

        for ($i = 0; $i < 2; ++$i) {
            $layer = new ZoomMapLayer($seed + 100 + $i, $layer, ZoomType::BLURRY);
        }

        for ($i = 0; $i < 2; $i++) {
            $layer = new ErosionMapLayer($seed + 3 + $i, $layer);
        }

        if (!$isUHC) {
            $layer = new DeepOceanMapLayer($seed + 4, $layer);
        }

        $layerMountains = new BiomeVariationMapLayer($seed + 200, $layer, null, $isUHC);

        for ($i = 0; $i < 2; $i++) {
            $layerMountains = new ZoomMapLayer($seed + 200 + $i, $layerMountains);
        }

        $layer = new BiomeMapLayer($seed + 5, $layer, $isUHC);
        for ($i = 0; $i < 2; $i++) {
            $layer = new ZoomMapLayer($seed + 200 + $i, $layer);
        }
        $layer = new BiomeEdgeMapLayer($seed + 200, $layer, $isUHC);
        $layer = new BiomeVariationMapLayer($seed + 200, $layer, $layerMountains, $isUHC);
        $layer = new RarePlainsMapLayer($seed + 201, $layer);
        $layer = new ZoomMapLayer($seed + 300, $layer);
        $layer = new ErosionMapLayer($seed + 6, $layer);
        $layer = new ZoomMapLayer($seed + 400, $layer);
        $layer = new BiomeThinEdgeMapLayer($seed + 400, $layer, $isUHC);
        $layer = new ShoreMapLayer($seed + 7, $layer, $isUHC);
        for ($i = 0; $i < $zoom; $i++) {
            $layer = new ZoomMapLayer($seed + 500 + $i, $layer);
        }

        $layerRiver = $layerMountains;
        $layerRiver = new ZoomMapLayer($seed + 300, $layerRiver);
        $layerRiver = new ZoomMapLayer($seed + 400, $layerRiver);
        for ($i = 0; $i < $zoom; $i++) {
            $layerRiver = new ZoomMapLayer($seed + 500 + $i, $layerRiver);
        }
        $layerRiver = new RiverMapLayer($seed + 10, $layerRiver, null, $isUHC);
        $layer = new RiverMapLayer($seed + 1000, $layerRiver, $layer, $isUHC);

        $layerLowerRes = $layer;
        for ($i = 0; $i < 2; $i++) {
            $layer = new ZoomMapLayer($seed + 2000 + $i, $layer);
        }

        $layer = new SmoothMapLayer($seed + 1001, $layer);

        return new MapLayerPair($layer, $layerLowerRes);
    }

    // TODO: Not sure what to do with the following methods
    public function clear(): void
    {
        $this->__destruct();
    }

    public function __destruct() {
        unset($this->random);
    }
}