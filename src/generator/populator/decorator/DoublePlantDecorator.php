<?php

namespace JonasWindmann\Giganilla\generator\populator\decorator;

use JonasWindmann\Giganilla\generator\populator\decorator\objects\DoubleTallPlant;
use JonasWindmann\Giganilla\GigaRandom;
use pocketmine\block\Block;
use pocketmine\world\ChunkManager;

class DoublePlantDecorator extends Decorator {
    private $decorations = [];

    public function setDoublePlants(array $doublePlants): void {
        $this->decorations = $doublePlants;
    }

    public function decorate(ChunkManager $world, GigaRandom $random, int $chunkX, int $chunkZ): void {
        $chunk = $world->getChunk($chunkX, $chunkZ);

        $x = $random->nextIntWithBound(16);
        $z = $random->nextIntWithBound(16);
        $sourceY = $random->nextIntWithBound($chunk->getHighestBlockAt($x, $z) + 32);

        $species = $this->getRandomDoublePlant($random);
        if ($species === null) {
            return;
        }

        (new DoubleTallPlant($species))->generate($world, $random, ($chunkX << 4) + $x, $sourceY, ($chunkZ << 4) + $z);
    }

    private function getRandomDoublePlant(GigaRandom $random): ?Block {
        $totalWeight = array_sum(array_column($this->decorations, 'weight'));

        $weight = $random->nextIntWithBound($totalWeight);
        foreach ($this->decorations as $deco) {
            $weight -= $deco['weight'];

            if ($weight < 0) {
                return $deco['block'];
            }
        }

        return null;
    }
}