<?php

namespace JonasWindmann\Giganilla\generator\populator;

use JonasWindmann\Giganilla\biome\BiomeList;
use JonasWindmann\Giganilla\generator\populator\decorator\objects\BirchTree;
use JonasWindmann\Giganilla\generator\populator\decorator\objects\BrownMushroomTree;
use JonasWindmann\Giganilla\generator\populator\decorator\objects\DarkOakTree;
use JonasWindmann\Giganilla\generator\populator\decorator\objects\GenericTree;
use JonasWindmann\Giganilla\generator\populator\decorator\objects\RedMushroomTree;

class RoofedForestPopulator extends ForestPopulator {
    private RedMushroomTree $redMushroomTree;
    private BrownMushroomTree $brownMushroomTree;
    private DarkOakTree $darkOakTree;

    public function initPopulators(): void
    {
        $this->genericTree = new GenericTree();
        $this->birchTree = new BirchTree();
        $this->redMushroomTree = new RedMushroomTree();
        $this->brownMushroomTree = new BrownMushroomTree();
        $this->darkOakTree = new DarkOakTree();

        $this->treeDecorator->setAmount(50);
        $this->treeDecorator->setTrees([
            [20, $this->genericTree],
            [5, $this->birchTree],
            [1, $this->redMushroomTree],
            [1, $this->brownMushroomTree],
            [20, $this->darkOakTree]
        ]);

        $this->tallGrassDecorator->setAmount(0);
    }

    public function getBiomes(): array
    {
        return [BiomeList::ROOFED_FOREST, BiomeList::ROOFED_FOREST_MOUNTAINS];
    }
}
