<?php

namespace JonasWindmann\Giganilla\generator\populator;

use JonasWindmann\Giganilla\biome\BiomeList;
use JonasWindmann\Giganilla\generator\populator\decorator\objects\BirchTree;
use JonasWindmann\Giganilla\generator\populator\decorator\objects\DoubleTallPlant;
use JonasWindmann\Giganilla\generator\populator\decorator\objects\GenericTree;
use JonasWindmann\Giganilla\GigaRandom;
use pocketmine\block\VanillaBlocks;
use pocketmine\world\ChunkManager;

class ForestPopulator extends BiomePopulator {
    protected GenericTree $genericTree;
    protected BirchTree $birchTree;
    protected int $doublePlantLoweringAmount = 3;
    protected static array $blocks = [];

    public function initPopulators(): void
    {
        $this->genericTree = new GenericTree();
        $this->birchTree = new BirchTree();

        if (empty(self::$blocks)) {
            self::$blocks = [
                VanillaBlocks::LILAC(),
                VanillaBlocks::ROSE_BUSH(),
                VanillaBlocks::PEONY()
            ];
        }

        $this->doublePlantDecorator->setAmount(0);
        $this->treeDecorator->setAmount(10);
        $this->treeDecorator->setTrees([
            [4, $this->genericTree],
            [1, $this->birchTree]
        ]);
        $this->tallGrassDecorator->setAmount(2);
    }

    public function onGroundPopulation(ChunkManager $world, GigaRandom $random, int $chunkX, int $chunkZ): void
    {
        $chunk = $world->getChunk($chunkX, $chunkZ);
        $sourceX = $chunkX << 4;
        $sourceZ = $chunkZ << 4;
        $amount = rand(0, 5) - $this->doublePlantLoweringAmount;
        $i = 0;
        while ($i < $amount) {
            for ($j = 0; $j < 5 && $i < $amount; $j++, $i++) {
                $xr = rand(0, 15);
                $zr = rand(0, 15);
                $x = $sourceX + $xr;
                $z = $sourceZ + $zr;
                $y = rand(0, $chunk->getHighestBlockAt($xr, $zr) + 32);
                $species = self::$blocks[rand(0, 2)];
                $dtp = new DoubleTallPlant($species);
                if ($dtp->generate($world, $random, $x, $y, $z) === true) {
                    $i++;
                    break;
                }
            }
        }

        parent::onGroundPopulation($world, $random, $chunkX, $chunkZ);
    }

    public function getBiomes(): array
    {
        return [BiomeList::FOREST, BiomeList::FOREST_HILLS];
    }
}
