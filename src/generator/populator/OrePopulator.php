<?php

namespace JonasWindmann\Giganilla\generator\populator;

use JonasWindmann\Giganilla\generator\populator\decorator\objects\OreVein;
use JonasWindmann\Giganilla\GigaRandom;
use pocketmine\block\VanillaBlocks;
use pocketmine\world\ChunkManager;

class OrePopulator implements IPopulator {
    private array $ores = [];

    public function __construct() {
        $this->addOre(new OreType(VanillaBlocks::DIRT(), 0, 256, 32, 10));
        $this->addOre(new OreType(VanillaBlocks::GRAVEL(), 0, 256, 32, 8));
        $this->addOre(new OreType(VanillaBlocks::GRANITE(), 0, 80, 32, 10));
        $this->addOre(new OreType(VanillaBlocks::DIORITE(), 0, 80, 32, 10));
        $this->addOre(new OreType(VanillaBlocks::ANDESITE(), 0, 80, 32, 10));
        $this->addOre(new OreType(VanillaBlocks::COAL_ORE(), 0, 128, 16, 20));
        $this->addOre(new OreType(VanillaBlocks::IRON_ORE(), 0, 64, 8, 20));
        $this->addOre(new OreType(VanillaBlocks::GOLD_ORE(), 0, 32, 8, 2));
        $this->addOre(new OreType(VanillaBlocks::REDSTONE_ORE(), 0, 16, 7, 8));
        $this->addOre(new OreType(VanillaBlocks::DIAMOND_ORE(), 0, 16, 7, 1));
        $this->addOre(new OreType(VanillaBlocks::LAPIS_LAZULI_ORE(), 16, 16, 6, 1));
    }

    public function populate(ChunkManager $world, GigaRandom $random, int $chunkX, int $chunkZ): void {
        $cx = $chunkX << 4;
        $cz = $chunkZ << 4;

        foreach ($this->ores as $oreType) {
            for ($n = 0; $n < $oreType->total; ++$n) {
                $sourceX = $cx + $random->nextIntWithBound(16);
                $sourceZ = $cz + $random->nextIntWithBound(16);
                $sourceY = $oreType->getRandomHeight($random);

                (new OreVein($oreType))->generate($world, $random, $sourceX, $sourceY, $sourceZ);
            }
        }
    }

    protected function addOre(OreType $ore): void {
        $this->ores[] = $ore;
    }
}