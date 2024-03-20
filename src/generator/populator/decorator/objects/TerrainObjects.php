<?php

namespace JonasWindmann\Giganilla\generator\populator\decorator\objects;

use pocketmine\block\VanillaBlocks;
use pocketmine\world\ChunkManager;
use pocketmine\world\World;

class TerrainObjects {
    public static function killWeakBlocksAbove(ChunkManager $world, int $x, int $y, int $z): bool
    {
        $curY = $y + 1;
        $changed = false;

        while ($curY < World::Y_MAX) {
            $block = $world->getBlockAt($x, $curY, $z);

            // TODO: This is not tested code, but it should work "technically"!!!!
            if (!$block->isSameState(VanillaBlocks::WATER()) || !$block->isSameState(VanillaBlocks::LAVA()) || !$block->isSameState(VanillaBlocks::AIR())) {
                break;
            }

            $world->setBlockAt($x, $curY, $z, VanillaBlocks::AIR());
            $changed = true;
            ++$curY;
        }

        return $changed;
    }
}