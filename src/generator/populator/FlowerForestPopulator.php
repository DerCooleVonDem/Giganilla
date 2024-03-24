<?php

namespace JonasWindmann\Giganilla\generator\populator;

use JonasWindmann\Giganilla\biome\BiomeList;
use JonasWindmann\Giganilla\generator\populator\decorator\objects\Flower;
use JonasWindmann\Giganilla\GigaRandom;
use JonasWindmann\Giganilla\noise\octave\SimplexOctaveGenerator;
use pocketmine\block\VanillaBlocks;
use pocketmine\world\ChunkManager;

class FlowerForestPopulator extends ForestPopulator {
    private static array $flowers = [];
    private SimplexOctaveGenerator $noiseGen;

    public function initPopulators(): void
    {
        if (empty(self::$flowers)) {
            self::$flowers = [
                VanillaBlocks::POPPY(),
                VanillaBlocks::POPPY(),
                VanillaBlocks::DANDELION(),
                VanillaBlocks::ALLIUM(),
                VanillaBlocks::AZURE_BLUET(),
                VanillaBlocks::RED_TULIP(),
                VanillaBlocks::ORANGE_TULIP(),
                VanillaBlocks::WHITE_TULIP(),
                VanillaBlocks::PINK_TULIP(),
                VanillaBlocks::OXEYE_DAISY()
            ];
        }

        parent::initPopulators();

        $this->treeDecorator->setAmount(6);
        $this->flowerDecorator->setAmount(0);

        $this->doublePlantLoweringAmount = 1;

        $random = new GigaRandom(2345);
        $this->noiseGen = new SimplexOctaveGenerator($random, 1, 0, 0, 0);
        $this->noiseGen->setScale(1 / 48);
    }

    public function onGroundPopulation(ChunkManager $world, GigaRandom $random, int $chunkX, int $chunkZ): void
    {
        parent::onGroundPopulation($world, $random, $chunkX, $chunkZ);

        $sourceX = $chunkX << 4;
        $sourceZ = $chunkZ << 4;

        $chunk = $world->getChunk($chunkX, $chunkZ);

        for ($i = 0; $i < 100; $i++) {
            $xr = $random->nextIntWithBound(16);
            $zr = $random->nextIntWithBound(16);
            $x = $sourceX + $xr;
            $z = $sourceZ + $zr;
            $y = $random->nextIntWithBound($chunk->getHighestBlockAt($xr, $zr) + 32);
            $noise = ($this->noiseGen->noise($x, $z, 0.5, 0, 2.0, false) + 1.0) / 2.0;
            $noise = $noise < 0 ? 0 : (min($noise, 0.9999));
            $flower = self::$flowers[(int) ($noise * 9)];
            (new Flower($flower))->generate($world, $random, $x, $y, $z);
        }
    }

    public function getBiomes(): array
    {
        return [BiomeList::FLOWER_FOREST];
    }
}