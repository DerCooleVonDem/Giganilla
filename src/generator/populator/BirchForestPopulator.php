<?php

namespace JonasWindmann\Giganilla\generator\populator;

use JonasWindmann\Giganilla\biome\BiomeList;
use JonasWindmann\Giganilla\generator\populator\decorator\objects\BirchTree;

class BirchForestPopulator extends ForestPopulator {
    public function initPopulators(): void
    {
        $this->birchTree = new BirchTree();

        $this->treeDecorator->setAmount(10);
        $this->treeDecorator->setTrees([
            [1, $this->birchTree]
        ]);
    }

    public function getBiomes(): array
    {
        return [BiomeList::BIRCH_FOREST, BiomeList::BIRCH_FOREST_HILLS];
    }
}
