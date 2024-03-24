<?php

namespace JonasWindmann\Giganilla\generator\populator;

use JonasWindmann\Giganilla\biome\BiomeList;
use JonasWindmann\Giganilla\generator\populator\decorator\MushroomDecorator;
use JonasWindmann\Giganilla\generator\populator\decorator\objects\SwampTree;
use JonasWindmann\Giganilla\generator\populator\decorator\WaterLilyDecorator;
use JonasWindmann\Giganilla\GigaRandom;
use pocketmine\block\VanillaBlocks;
use pocketmine\world\ChunkManager;

class SwamplandPopulator extends BiomePopulator {
    private MushroomDecorator $swamplandBrownMushroomDecorator;
    private MushroomDecorator $swamplandRedMushroomDecorator;
    private WaterLilyDecorator $waterLilyDecorator;
    private SwampTree $swampTree;

    public function initPopulators(): void {
        $this->sandPatchDecorator->setAmount(0);
        $this->gravelPatchDecorator->setAmount(2);
        $this->treeDecorator->setAmount(2);
        $this->treeDecorator->setTrees([
            [1, SwampTree::class]
        ]);
        $this->flowerDecorator->setAmount(1);
        $this->flowerDecorator->setFlowers([
            [1, VanillaBlocks::BLUE_ORCHID()]
        ]);
        $this->tallGrassDecorator->setAmount(5);
        $this->sugarCaneDecorator->setAmount(20);
        $this->swamplandBrownMushroomDecorator->setAmount(8);
        $this->swamplandBrownMushroomDecorator->setDensity(0.01);
        $this->swamplandBrownMushroomDecorator->setUseFixedHeightRange();
        $this->swamplandRedMushroomDecorator->setAmount(8);
        $this->swamplandRedMushroomDecorator->setDensity(0.01);
        $this->swamplandRedMushroomDecorator->setUseFixedHeightRange();
        $this->waterLilyDecorator->setAmount(4);
    }

    public function getBiomes(): array {
        return [BiomeList::SWAMPLAND, BiomeList::SWAMPLAND_MOUNTAINS];
    }

    public function onGroundPopulation(ChunkManager $world, GigaRandom $random, int $chunkX, int $chunkZ): void {
        parent::onGroundPopulation($world, $random, $chunkX, $chunkZ);

        $this->swamplandBrownMushroomDecorator->populate($world, $random, $chunkX, $chunkZ);
        $this->swamplandRedMushroomDecorator->populate($world, $random, $chunkX, $chunkZ);
        $this->waterLilyDecorator->populate($world, $random, $chunkX, $chunkZ);
    }
}