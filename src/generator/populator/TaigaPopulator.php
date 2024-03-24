<?php

namespace JonasWindmann\Giganilla\generator\populator;

use JonasWindmann\Giganilla\biome\BiomeList;
use JonasWindmann\Giganilla\generator\populator\decorator\MushroomDecorator;
use JonasWindmann\Giganilla\generator\populator\decorator\objects\RedwoodTree;
use JonasWindmann\Giganilla\generator\populator\decorator\objects\TallRedwoodTree;
use JonasWindmann\Giganilla\GigaRandom;
use pocketmine\block\VanillaBlocks;
use pocketmine\world\ChunkManager;

class TaigaPopulator extends BiomePopulator {
    protected MushroomDecorator $taigaBrownMushroomDecorator;
    protected MushroomDecorator $taigaRedMushroomDecorator;
    private RedwoodTree $redwoodTree;
    private TallRedwoodTree $tallRedwoodTree;

    public function initPopulators(): void {
        $this->taigaBrownMushroomDecorator = new MushroomDecorator(VanillaBlocks::BROWN_MUSHROOM());
        $this->taigaRedMushroomDecorator = new MushroomDecorator(VanillaBlocks::RED_MUSHROOM());
        $this->redwoodTree = new RedwoodTree();
        $this->tallRedwoodTree = new TallRedwoodTree();

        $this->doublePlantDecorator->setAmount(7);
        $this->doublePlantDecorator->setDoublePlants([
            [1, VanillaBlocks::LARGE_FERN()]
        ]);
        $this->treeDecorator->setAmount(10);
        $this->treeDecorator->setTrees([
            [2, RedwoodTree::class],
            [1, TallRedwoodTree::class]
        ]);
        $this->tallGrassDecorator->setDensity(0.8);
        $this->deadBushDecorator->setAmount(1);
        $this->taigaBrownMushroomDecorator->setAmount(1);
        $this->taigaBrownMushroomDecorator->setUseFixedHeightRange();
        $this->taigaBrownMushroomDecorator->setDensity(0.25);
        $this->taigaRedMushroomDecorator->setAmount(1);
        $this->taigaRedMushroomDecorator->setDensity(0.125);
    }

    public function getBiomes(): array {
        return [
            BiomeList::TAIGA,
            BiomeList::TAIGA_HILLS,
            BiomeList::TAIGA_MOUNTAINS,
            BiomeList::COLD_TAIGA,
            BiomeList::COLD_TAIGA_HILLS,
            BiomeList::COLD_TAIGA_MOUNTAINS
        ];
    }

    public function onGroundPopulation(ChunkManager $world, GigaRandom $random, int $chunkX, int $chunkZ): void {
        parent::onGroundPopulation($world, $random, $chunkX, $chunkZ);

        $this->taigaBrownMushroomDecorator->decorate($world, $random, $chunkX, $chunkZ);
        $this->taigaRedMushroomDecorator->decorate($world, $random, $chunkX, $chunkZ);
    }
}