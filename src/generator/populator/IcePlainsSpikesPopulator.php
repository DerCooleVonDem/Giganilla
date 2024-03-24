<?php

namespace JonasWindmann\Giganilla\generator\populator;

use JonasWindmann\Giganilla\biome\BiomeList;

class IcePlainsSpikesPopulator extends IcePlainsPopulator {

    public function initPopulators(): void {
        parent::initPopulators();
        $this->tallGrassDecorator->setAmount(0);
        // TODO: Implement Ice decorator.
    }

    public function getBiomes(): array {
        return [BiomeList::ICE_PLAINS_SPIKES];
    }
}
