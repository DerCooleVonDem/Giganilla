<?php

namespace JonasWindmann\Giganilla\generator\populator;

use JonasWindmann\Giganilla\biome\BiomeList;

class BirchForestMountainsPopulator extends ForestPopulator {
    protected TallBirchTree $tallBirchTree;

    public function initPopulators(): void
    {
        $this->treeDecorator->setTrees([
            [1, $this->birchTree],
            [1, $this->tallBirchTree]
        ]);
    }

    public function getBiomes(): array
    {
        return [BiomeList::BIRCH_FOREST_MOUNTAINS, BiomeList::BIRCH_FOREST_HILLS_MOUNTAINS];
    }
}
