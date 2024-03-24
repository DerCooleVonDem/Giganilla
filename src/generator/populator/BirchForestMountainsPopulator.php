<?php

namespace JonasWindmann\Giganilla\generator\populator;

use JonasWindmann\Giganilla\biome\BiomeList;
use JonasWindmann\Giganilla\generator\populator\decorator\objects\BirchTree;
use JonasWindmann\Giganilla\generator\populator\decorator\objects\TallBirchTree;

class BirchForestMountainsPopulator extends ForestPopulator {
    protected TallBirchTree $tallBirchTree;

    public function initPopulators(): void
    {
        $this->birchTree = new BirchTree();
        $this->tallBirchTree = new TallBirchTree();

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
