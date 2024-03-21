<?php

namespace JonasWindmann\Giganilla\generator\populator\decorator\objects;

use JonasWindmann\Giganilla\GigaRandom;
use pocketmine\block\VanillaBlocks;
use pocketmine\world\ChunkManager;

class Flower extends TerrainObjects {
    private $type;

    public function __construct($block) {
        $this->type = $block;
    }

    public function generate(ChunkManager $world, GigaRandom $random, int $sourceX, int $sourceY, int $sourceZ): bool {
        $placed = false;
        $height = $world->getMaxY();
        for ($i = 0; $i < 64; ++$i) {
            $targetX = $sourceX + $random->nextIntWithBound(8) - $random->nextIntWithBound(8);
            $targetZ = $sourceZ + $random->nextIntWithBound(8) - $random->nextIntWithBound(8);
            $targetY = $sourceY + $random->nextIntWithBound(4) - $random->nextIntWithBound(4);

            $block = $world->getBlockAt($targetX, $targetY, $targetZ);
            $blockBelow = $world->getBlockAt($targetX, $targetY - 1, $targetZ);
            if ($sourceY < $height && $block->hasSameTypeId(VanillaBlocks::AIR()) && $blockBelow->hasSameTypeId(VanillaBlocks::GRASS())) {
                $world->setBlockAt($targetX, $targetY, $targetZ, $this->type);
                $placed = true;
            }
        }

        return $placed;
    }
}