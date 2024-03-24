<?php

namespace JonasWindmann\Giganilla\generator\populator;

use JonasWindmann\Giganilla\biome\BiomeList;

class DesertPopulator extends BiomePopulator {
    public function initPopulators(): void
    {
        $this->waterLakeDecorator->setAmount(0);
        $this->deadBushDecorator->setAmount(2);
        $this->sugarCaneDecorator->setAmount(60);
        $this->cactusDecorator->setAmount(3);
    }

    public function getBiomes(): array
    {
        return [BiomeList::DESERT, BiomeList::DESERT_HILLS];
    }
}