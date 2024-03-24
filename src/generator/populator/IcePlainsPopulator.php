<?php

namespace JonasWindmann\Giganilla\generator\populator;

use JonasWindmann\Giganilla\biome\BiomeList;
use JonasWindmann\Giganilla\generator\populator\decorator\objects\RedwoodTree;

class IcePlainsPopulator extends BiomePopulator {
    private RedwoodTree $redwoodTree;

    public function __construct() {
        parent::__construct();
        $this->redwoodTree = new RedwoodTree();
    }

    public function initPopulators(): void {
        $this->treeDecorator->setAmount(0);
        $this->treeDecorator->setTrees([
            [1, $this->redwoodTree]
        ]);
        $this->flowerDecorator->setAmount(0);
    }

    public function getBiomes(): array {
        return [BiomeList::ICE_PLAINS, BiomeList::ICE_MOUNTAINS];
    }
}
