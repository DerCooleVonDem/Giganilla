<?php

namespace JonasWindmann\Giganilla\generator\populator;

use JonasWindmann\Giganilla\biome\BiomeList;
use JonasWindmann\Giganilla\generator\populator\decorator\objects\AcaciaTree;
use JonasWindmann\Giganilla\generator\populator\decorator\objects\GenericTree;
use pocketmine\block\VanillaBlocks;

class SavannaMountainsPopulator extends SavannaPopulator {

    private AcaciaTree $acaciaTree;
    private GenericTree $genericTree;

    public function initPopulators(): void {
        $this->acaciaTree = new AcaciaTree();
        $this->genericTree = new GenericTree();
        $this->treeDecorator->setAmount(2);
        $this->treeDecorator->setTrees([
            [4, $this->acaciaTree],
            [4, $this->genericTree]
        ]);
        $this->flowerDecorator->setAmount(2);
        $this->tallGrassDecorator->setAmount(5);
        $this->doublePlantDecorator->setDoublePlants([
            [1, VanillaBlocks::DOUBLE_TALLGRASS()]
        ]);
    }

    public function getBiomes(): array {
        return [BiomeList::SAVANNA_MOUNTAINS, BiomeList::SAVANNA_PLATEAU_MOUNTAINS];
    }
}