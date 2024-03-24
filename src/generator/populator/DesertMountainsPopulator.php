<?php

namespace JonasWindmann\Giganilla\generator\populator;

use JonasWindmann\Giganilla\biome\BiomeList;

class DesertMountainsPopulator extends DesertPopulator {
    public function initPopulators(): void
    {
        $this->waterLakeDecorator->setAmount(1);
    }

    public function getBiomes(): array
    {
        return [BiomeList::DESERT_MOUNTAINS];
    }
}
