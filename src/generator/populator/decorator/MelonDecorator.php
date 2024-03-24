<?php

namespace JonasWindmann\Giganilla\generator\populator\decorator;

use JonasWindmann\Giganilla\GigaRandom;
use pocketmine\block\VanillaBlocks;
use pocketmine\world\ChunkManager;

class MelonDecorator extends Decorator
{
    public function decorate(ChunkManager $world, GigaRandom $random, int $chunkX, int $chunkZ): void
    {
        $sourceX = ($chunkX << 4) + $random->nextIntWithBound(16);
        $sourceZ = ($chunkZ << 4) + $random->nextIntWithBound(16);
        $sourceY = $random->nextIntWithBound((64 << 1));

        for ($i = 0; $i < 64; $i++) {
            $x = $sourceX + $random->nextIntWithBound(8) - $random->nextIntWithBound(8);
            $z = $sourceZ + $random->nextIntWithBound(8) - $random->nextIntWithBound(8);
            $y = $sourceY + $random->nextIntWithBound(4) - $random->nextIntWithBound(4);

            $block = $world->getBlockAt($x, $y, $z);
            $blockBelow = $world->getBlockAt($x, $y - 1, $z);

            if ($block->hasSameTypeId(VanillaBlocks::AIR()) && $blockBelow->hasSameTypeId(VanillaBlocks::GRASS())) {
                $world->setBlockAt($x, $y, $z, VanillaBlocks::MELON());
            }
        }
    }
}
