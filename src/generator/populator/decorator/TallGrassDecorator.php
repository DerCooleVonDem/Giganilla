<?php

namespace JonasWindmann\Giganilla\generator\populator\decorator;

use JonasWindmann\Giganilla\generator\populator\decorator\objects\TallGrass;
use JonasWindmann\Giganilla\GigaRandom;
use pocketmine\block\VanillaBlocks;
use pocketmine\world\ChunkManager;

class TallGrassDecorator extends Decorator {
    private $density = 0.0;

    public function setDensity(float $density): void {
        $this->density = $density;
    }

    public function decorate(ChunkManager $world, GigaRandom $random, int $chunkX, int $chunkZ): void {
        $x = $random->nextIntWithBound(16);
        $z = $random->nextIntWithBound(16);
        $topBlock = $world->getChunk($chunkX, $chunkZ)->getHighestBlockAt($x, $z);
        if ($topBlock <= 0) return; // Nothing to do if this column is empty

        $y = $random->nextIntWithBound(abs($topBlock << 1));

        // the grass species can change on each decoration pass
        $species = VanillaBlocks::TALL_GRASS();
        if ($this->density > 0 && $random->nextFloat() < $this->density) {
            $species = VanillaBlocks::FERN();
        }

        (new TallGrass($species))->generate($world, $random, ($chunkX << 4) + $x, $y, ($chunkZ << 4) + $z);
    }
}