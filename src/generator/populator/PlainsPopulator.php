<?php

namespace JonasWindmann\Giganilla\generator\populator;

use JonasWindmann\Giganilla\biome\BiomeList;
use JonasWindmann\Giganilla\generator\populator\decorator\objects\DoubleTallPlant;
use JonasWindmann\Giganilla\generator\populator\decorator\objects\Flower;
use JonasWindmann\Giganilla\generator\populator\decorator\objects\TallGrass;
use JonasWindmann\Giganilla\GigaRandom;
use JonasWindmann\Giganilla\noise\octave\SimplexOctaveGenerator;
use pocketmine\block\VanillaBlocks;
use pocketmine\world\ChunkManager;

class PlainsPopulator extends BiomePopulator {
    private array $plainsFlowers = [];
    private array $tulips = [];
    private GigaRandom $internalRandom;
    private SimplexOctaveGenerator $noiseGen;

    public function __construct() {
        $this->internalRandom = new GigaRandom(2345);
        $this->noiseGen = new SimplexOctaveGenerator($this->internalRandom, 1, 0, 0, 0);
    }

    public function initPopulators(): void {
        if (empty($this->plainsFlowers)) {
            $this->plainsFlowers = [
                VanillaBlocks::POPPY(),
                VanillaBlocks::AZURE_BLUET(),
                VanillaBlocks::OXEYE_DAISY()
            ];
        }

        if (empty($this->tulips)) {
            $this->tulips = [
                VanillaBlocks::RED_TULIP(),
                VanillaBlocks::ORANGE_TULIP(),
                VanillaBlocks::WHITE_TULIP(),
                VanillaBlocks::PINK_TULIP()
            ];
        }

        parent::initPopulators();
    }

    public function getBiomes(): array {
        return [BiomeList::PLAINS];
    }

    public function onGroundPopulation(ChunkManager $world, GigaRandom $random, int $chunkX, int $chunkZ): void {
        $sourceX = $chunkX << 4;
        $sourceZ = $chunkZ << 4;

        $flowerAmount = 15;
        $tallGrassAmount = 5;
        if ($this->noiseGen->noise($sourceX + 8, $sourceZ + 8, 0, 0.5, 2.0, false) >= -0.8) {
            $flowerAmount = 4;
            $tallGrassAmount = 10;
            for ($i = 0; $i < 7; $i++) {
                $x = $sourceX + $random->nextIntWithBound(16);
                $z = $sourceZ + $random->nextIntWithBound(16);
                $y = $random->nextIntWithBound($world->getChunk($chunkX, $chunkZ)->getHighestBlockAt($x - $sourceX, $z - $sourceZ) + 32);
                (new DoubleTallPlant(VanillaBlocks::DOUBLE_TALLGRASS()))->generate($world, $random, $x, $y, $z);
            }
        }

        $flower = VanillaBlocks::DANDELION();
        if ($this->noiseGen->noise($sourceX + 8, $sourceZ + 8, 0, 0.5, 2.0, false) < -0.8) {
            $flower = $this->tulips[$random->nextIntWithBound(2)];
        } elseif ($random->nextIntWithBound(3) > 0) {
            $flower = $this->plainsFlowers[$random->nextIntWithBound(3)];
        }

        for ($i = 0; $i < $flowerAmount; $i++) {
            $x = $sourceX + $random->nextIntWithBound(16);
            $z = $sourceZ + $random->nextIntWithBound(16);
            $y = $random->nextIntWithBound($world->getChunk($chunkX, $chunkZ)->getHighestBlockAt($x - $sourceX, $z - $sourceZ) + 32);
            (new Flower($flower))->generate($world, $random, $x, $y, $z);
        }

        for ($i = 0; $i < $tallGrassAmount; $i++) {
            $x = $sourceX + $random->nextIntWithBound(16);
            $z = $sourceZ + $random->nextIntWithBound(16);
            $y = $random->nextIntWithBound($world->getChunk($chunkX, $chunkZ)->getHighestBlockAt($x - $sourceX, $z - $sourceZ) << 1);
            (new TallGrass(VanillaBlocks::TALL_GRASS()))->generate($world, $random, $x, $y, $z);
        }

        parent::onGroundPopulation($world, $random, $chunkX, $chunkZ);
    }
}