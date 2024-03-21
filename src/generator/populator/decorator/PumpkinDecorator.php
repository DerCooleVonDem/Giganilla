<?php

namespace JonasWindmann\Giganilla\generator\populator\decorator;

use JonasWindmann\Giganilla\GigaRandom;
use pocketmine\block\VanillaBlocks;
use pocketmine\world\ChunkManager;

class PumpkinDecorator extends Decorator {
    public function decorate(ChunkManager $world, GigaRandom $random, int $chunkX, int $chunkZ): void {
        if ($random->nextIntWithBound(32) == 0) {
            $sourceX = ($chunkX << 4) + $random->nextIntWithBound(16);
            $sourceZ = ($chunkZ << 4) + $random->nextIntWithBound(16);
            // TODO: Test this chunk getting method
            $sourceY = $random->nextIntWithBound($world->getChunk($chunkX, $chunkZ)->getHighestBlockAt($sourceX - ($chunkX << 4), $sourceZ - ($chunkZ << 4)) << 1);

            for ($i = 0; $i < 64; ++$i) {
                $x = $sourceX + $random->nextIntWithBound(8) - $random->nextIntWithBound(8);
                $z = $sourceZ + $random->nextIntWithBound(8) - $random->nextIntWithBound(8);
                $y = $sourceY + $random->nextIntWithBound(4) - $random->nextIntWithBound(4);

                if ($world->getBlockAt($x, $y, $z)->hasSameTypeId(VanillaBlocks::AIR()) && $world->getBlockAt($x, $y - 1, $z)->hasSameTypeId(VanillaBlocks::GRASS())) {
                    $world->setBlockAt($x, $y, $z, VanillaBlocks::PUUMPKIN());
                }
            }
        }
    }
}