<?php

namespace JonasWindmann\Giganilla\generator\populator;

use JonasWindmann\Giganilla\biome\BiomeList;
use JonasWindmann\Giganilla\generator\populator\decorator\MelonDecorator;
use JonasWindmann\Giganilla\generator\populator\decorator\objects\CocoaTree;
use JonasWindmann\Giganilla\generator\populator\decorator\objects\JungleBush;
use JonasWindmann\Giganilla\generator\populator\decorator\objects\MegaJungleTree;
use JonasWindmann\Giganilla\GigaRandom;
use pocketmine\world\BlockTransaction;
use pocketmine\world\ChunkManager;

class JunglePopulator extends BiomePopulator {

    protected MelonDecorator $decorator;
    protected JungleBush $jungleBush;
    protected MegaJungleTree $megaJungleTree;
    protected CocoaTree $cocoaTree;

    public function initPopulators(): void
    {
        $this->treeDecorator->setAmount(65);
        $this->treeDecorator->setTrees([
            [10, $this->bigOakTree],
            [50, $this->jungleBush],
            [15, $this->megaJungleTree],
            [30, $this->cocoaTree]
        ]);
        $this->flowerDecorator->setAmount(4);
        $this->tallGrassDecorator->setAmount(25);
        $this->tallGrassDecorator->setDensity(0.25);
    }

    public function getBiomes(): array
    {
        return [BiomeList::JUNGLE, BiomeList::JUNGLE_HILLS, BiomeList::JUNGLE_MOUNTAINS];
    }

    public function onGroundPopulation(ChunkManager $world, GigaRandom $random, int $chunkX, int $chunkZ): void
    {
        $sourceX = $chunkX << 4;
        $sourceZ = $chunkZ << 4;

        $chunk = $world->getChunk($chunkX, $chunkZ);

        for ($i = 0; $i < 7; $i++) {
            $xr = $random->nextIntWithBound(16);
            $zr = $random->nextIntWithBound(16);
            $x = $sourceX + $xr;
            $z = $sourceZ + $zr;
            $y = $chunk->getHighestBlockAt($xr, $zr);

            $txn = new BlockTransaction($world);

            $bushTree = new JungleBush();
            $bushTree->initialize($random, $txn);

            if ($bushTree->generate($world, $random, $x, $y, $z)) {
                $txn->apply();
            }
        }

        parent::onGroundPopulation($world, $random, $chunkX, $chunkZ);
    }
}
