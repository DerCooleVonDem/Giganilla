<?php

namespace JonasWindmann\Giganilla\generator\populator\decorator;

use JonasWindmann\Giganilla\generator\populator\decorator\objects\Cactus;
use JonasWindmann\Giganilla\GigaRandom;
use pocketmine\world\ChunkManager;

class CactusDecorator extends Decorator {
    public function decorate(ChunkManager $world, GigaRandom $random, int $chunkX, int $chunkZ): void {
        $sourceX = $chunkX << 4;
        $sourceZ = $chunkZ << 4;
        $x = $random->nextIntWithBound(16);
        $z = $random->nextIntWithBound(16);

        $sourceY = $random->nextIntWithBound($world->getChunk($chunkX, $chunkZ)->getHighestBlockAt($x, $z) << 1);

        for ($l = 0; $l < 10; ++$l) {
            $i = $sourceX + $random->nextIntWithBound(8) - $random->nextIntWithBound(8);
            $k = $sourceZ + $random->nextIntWithBound(8) - $random->nextIntWithBound(8);
            $j = $sourceY + $random->nextIntWithBound(4) - $random->nextIntWithBound(4);

            Cactus::generate($world, $random, $x + $i, $j, $z + $k);
        }
    }
}