<?php

namespace JonasWindmann\Giganilla\generator\populator;

use JonasWindmann\Giganilla\biome\BiomeList;
use JonasWindmann\Giganilla\generator\populator\decorator\objects\AcaciaTree;
use JonasWindmann\Giganilla\generator\populator\decorator\objects\GenericTree;
use pocketmine\block\VanillaBlocks;

class SavannaPopulator extends BiomePopulator {
    private AcaciaTree $acaciaTree;
    private GenericTree $genericTree;

    public function initPopulators(): void {
        $this->acaciaTree = new AcaciaTree();
        $this->genericTree = new GenericTree();
        $this->doublePlantDecorator->setAmount(7);
        $this->doublePlantDecorator->setDoublePlants([
            [1, VanillaBlocks::DOUBLE_TALLGRASS()]
        ]);
        $this->treeDecorator->setAmount(1);
        $this->treeDecorator->setTrees([
            [4, $this->acaciaTree],
            [4, $this->genericTree]
        ]);
        $this->flowerDecorator->setAmount(4);
        $this->tallGrassDecorator->setAmount(20);
    }

    public function getBiomes(): array {
        return [BiomeList::SAVANNA, BiomeList::SAVANNA_PLATEAU];
    }
}