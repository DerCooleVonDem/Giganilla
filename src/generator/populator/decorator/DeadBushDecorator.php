<?php

namespace JonasWindmann\Giganilla\generator\populator\decorator;

use JonasWindmann\Giganilla\GigaRandom;
use pocketmine\block\VanillaBlocks;
use pocketmine\world\ChunkManager;

class DeadBushDecorator extends Decorator {
    public function decorate(ChunkManager $world, GigaRandom $random, int $chunkX, int $chunkZ): void {
        $sourceX = ($chunkX << 4) + $random->nextIntWithBound(16);
        $sourceZ = ($chunkZ << 4) + $random->nextIntWithBound(16);
        $sourceY = $world->getChunk($chunkX, $chunkZ)->getHighestBlockAt($sourceX & 0x0f, $sourceZ & 0x0f);
        $sourceY = $random->nextIntWithBound($sourceY << 1);

        while ($sourceY > 0 && ($world->getBlockAt($sourceX, $sourceY, $sourceZ)->hasSameTypeId(VanillaBlocks::AIR()) || $world->getBlockAt($sourceX, $sourceY, $sourceZ)->hasSameTypeId(VanillaBlocks::BIRCH_LEAVES()))) {
            --$sourceY;
        }

        for ($i = 0; $i < 4; ++$i) {
            $x = $sourceX + $random->nextIntWithBound(8) - $random->nextIntWithBound(8);
            $z = $sourceZ + $random->nextIntWithBound(8) - $random->nextIntWithBound(8);
            $y = $sourceY + $random->nextIntWithBound(4) - $random->nextIntWithBound(4);

            if ($world->getBlockAt($x, $y, $z)->hasSameTypeId(VanillaBlocks::AIR())) {
                $blockBelow = $world->getBlockAt($x, $y - 1, $z);

                // The switch removed some calls, interesting that this can do the same job as cpp in fewer iterations and lines. But still slower but more accessible...
                foreach ([VanillaBlocks::SAND(), VanillaBlocks::DIRT(), VanillaBlocks::HARDENED_CLAY(), VanillaBlocks::STAINED_CLAY()] as $block) {
                    if ($block == $blockBelow) {
                        $world->setBlockAt($x, $y, $z, VanillaBlocks::DEAD_BUSH());
                        break;
                    }
                }
            }
        }
    }
}