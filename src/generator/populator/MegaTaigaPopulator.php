<?php

namespace JonasWindmann\Giganilla\generator\populator;

use JonasWindmann\Giganilla\biome\BiomeList;
use JonasWindmann\Giganilla\generator\populator\decorator\objects\MegaPineTree;
use JonasWindmann\Giganilla\generator\populator\decorator\objects\MegaSpruceTree;

class MegaTaigaPopulator extends TaigaPopulator {
    private MegaPineTree $megaPineTree;
    private MegaSpruceTree $megaSpruceTree;

    public function initPopulators(): void {
        $this->treeDecorator->setAmount(10);
        $this->treeDecorator->setTrees([
            [52, MegaPineTree::class],
            [26, MegaSpruceTree::class],
            [36, MegaPineTree::class],
            [3, MegaSpruceTree::class]
        ]);
        $this->deadBushDecorator->setAmount(0);
        $this->taigaBrownMushroomDecorator->setAmount(3);
        $this->taigaBrownMushroomDecorator->setUseFixedHeightRange();
        $this->taigaBrownMushroomDecorator->setDensity(0.25);
        $this->taigaRedMushroomDecorator->setAmount(3);
        $this->taigaRedMushroomDecorator->setDensity(0.125);
    }

    public function getBiomes(): array {
        return [
            BiomeList::MEGA_TAIGA,
            BiomeList::MEGA_TAIGA_HILLS
        ];
    }
}
