<?php

namespace JonasWindmann\Giganilla\generator\populator\decorator;

use JonasWindmann\Giganilla\generator\populator\decorator\objects\SugarCane;
use JonasWindmann\Giganilla\GigaRandom;
use pocketmine\world\ChunkManager;

class SugarCaneDecorator extends Decorator {
    public function decorate(ChunkManager $world, GigaRandom $random, int $chunkX, int $chunkZ): void {
        $sourceX = ($chunkX << 4) + $random->nextIntWithBound(16);
        $sourceZ = ($chunkZ << 4) + $random->nextIntWithBound(16);
        $maxY = $world->getChunk($chunkX, $chunkZ)->getHighestBlockAt($sourceX & 0x0f, $sourceZ & 0x0f);

        if ($maxY <= 0) {
            return;
        }

        $sourceY = $random->nextIntWithBound($maxY << 1);
        for ($j = 0; $j < 20; ++$j) {
            $x = $sourceX + $random->nextIntWithBound(4) - $random->nextIntWithBound(4);
            $z = $sourceZ + $random->nextIntWithBound(4) - $random->nextIntWithBound(4);

            SugarCane::generate($world, $random, $x, $sourceY, $z);
        }
    }
}