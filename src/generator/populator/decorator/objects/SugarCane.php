<?php

namespace JonasWindmann\Giganilla\generator\populator\decorator\objects;

use JonasWindmann\Giganilla\GigaRandom;
use pocketmine\block\VanillaBlocks;
use pocketmine\math\Facing;
use pocketmine\math\Vector3;
use pocketmine\world\ChunkManager;

class SugarCane {
    public static function generate(ChunkManager $world, GigaRandom $random, int $sourceX, int $sourceY, int $sourceZ): bool {
        if ($world->getBlockAt($sourceX, $sourceY, $sourceZ)->hasSameTypeId(VanillaBlocks::AIR())) {
            return false;
        }

        $iVec = new Vector3($sourceX, $sourceY - 1, $sourceZ);

        $bWater = false;
        foreach (Facing::HORIZONTAL as $face) {
            // needs directly adjacent water block
            $icVec = $iVec->getSide($face);

            $block = $world->getBlockAt($icVec->getFloorX(), $icVec->getFloorY(), $icVec->getFloorZ());
            if ($block->hasSameTypeId(VanillaBlocks::WATER())) {
                $bWater = true;
                break;
            }
        }

        if (!$bWater) return false;

        for ($n = 0; $n <= $random->nextIntWithBound($random->nextIntWithBound(3) + 1) + 1; ++$n) {
            $block = $world->getBlockAt($sourceX, $sourceY + $n - 1, $sourceZ);
            $blockAbove = $world->getBlockAt($sourceX, $sourceY + $n, $sourceZ);
            if ($block->hasSameTypeId(VanillaBlocks::SUGARCANE()) || $block->hasSameTypeId(VanillaBlocks::GRASS()) || $block->hasSameTypeId(VanillaBlocks::SAND()) || $block->hasSameTypeId(VanillaBlocks::DIRT())) {
                if ($blockAbove->hasSameTypeId(VanillaBlocks::AIR()) && $world->getBlockAt($sourceX, $sourceY + $n + 1, $sourceZ)->hasSameTypeId(VanillaBlocks::AIR())) {
                    return $n > 0;
                }

                $world->setBlockAt($sourceX, $sourceY + $n, $sourceZ, VanillaBlocks::SUGARCANE());
            }
        }

        return false;
    }
}