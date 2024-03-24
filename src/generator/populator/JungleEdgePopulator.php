<?php

namespace JonasWindmann\Giganilla\generator\populator;

use JonasWindmann\Giganilla\biome\BiomeList;
use JonasWindmann\Giganilla\generator\populator\decorator\objects\BigOakTree;
use JonasWindmann\Giganilla\generator\populator\decorator\objects\CocoaTree;

class JungleEdgePopulator extends JunglePopulator {
    public function initPopulators(): void {
        $this->cocoaTree = new CocoaTree();

        $this->treeDecorator->setAmount(2);
        $this->treeDecorator->setTrees([
            [10, BigOakTree::class],
            [45, CocoaTree::class]
        ]);
    }

    public function getBiomes(): array {
        return [BiomeList::JUNGLE_EDGE, BiomeList::JUNGLE_EDGE_MOUNTAINS];
    }
}