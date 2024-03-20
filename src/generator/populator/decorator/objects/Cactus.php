<?php

namespace JonasWindmann\Giganilla\generator\populator\decorator\objects;

use JonasWindmann\Giganilla\GigaRandom;
use pocketmine\block\VanillaBlocks;
use pocketmine\math\Facing;
use pocketmine\math\Vector3;
use pocketmine\world\ChunkManager;

class Cactus {
    public static function generate(ChunkManager $world, GigaRandom $random, int $sourceX, int $sourceY, int $sourceZ): bool
    {
        $block = $world->getBlockAt($sourceX, $sourceY, $sourceZ);
        if ($block->hasSameTypeId(VanillaBlocks::AIR())) {
            $height = $random->nextInt($random->nextInt(2) + 1) + 1;

            for ($n = $sourceY; $n <= $sourceY + $height; ++$n) {
                $belowBlock = $world->getBlockAt($sourceX, $n - 1, $sourceZ);
                if ($belowBlock->hasSameTypeId(VanillaBlocks::SAND()) || ($belowBlock->hasSameTypeId(VanillaBlocks::CACTUS()) && $world->getBlockAt($sourceX, $n + 1, $sourceZ)->hasSameTypeId(VanillaBlocks::AIR()))) {
                    $iVec = new Vector3($sourceX, $n, $sourceZ);

                    foreach (Facing::HORIZONTAL as $facing) {
                        $face = $iVec->getSide($facing); // Default step is 1
                        if ($world->getBlockAt($face['x'], $face['y'], $face['z'])->isSolid()) {
                            return $n > $sourceY;
                        }
                    }

                    $world->setBlockAt($sourceX, $n, $sourceZ, VanillaBlocks::CACTUS());
                }
            }
        }

        return true;
    }
}
