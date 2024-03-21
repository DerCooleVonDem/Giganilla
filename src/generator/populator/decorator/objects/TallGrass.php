<?php

namespace JonasWindmann\Giganilla\generator\populator\decorator\objects;

use JonasWindmann\Giganilla\GigaRandom;
use pocketmine\block\VanillaBlocks;
use pocketmine\world\ChunkManager;

class TallGrass extends TerrainObjects {
    private $grassType;

    public function __construct($grassType) {
        $this->grassType = $grassType;
    }

    public function generate(ChunkManager $world, GigaRandom $random, int $sourceX, int $sourceY, int $sourceZ): bool {
        $currentBlock = $world->getBlockAt($sourceX, $sourceY, $sourceZ);
        while (($currentBlock->hasSameTypeId(VanillaBlocks::AIR()) || $currentBlock->hasSameTypeId(VanillaBlocks::JUNGLE_LEAVES())) && $sourceY > 0) {
            --$sourceY;
            $currentBlock = $world->getBlockAt($sourceX, $sourceY, $sourceZ)->getTypeId();
        }
        ++$sourceY;

        $succeeded = false;
        $height = $world->getMaxY();
        for ($i = 0; $i < 128; ++$i) {
            $x = $sourceX + $random->nextIntWithBound(8) - $random->nextIntWithBound(8);
            $z = $sourceZ + $random->nextIntWithBound(8) - $random->nextIntWithBound(8);
            $y = $sourceY + $random->nextIntWithBound(4) - $random->nextIntWithBound(4);

            $block = $world->getBlockAt($x, $y, $z);
            $blockBelow = $world->getBlockAt($x, $y - 1, $z);
            if ($y < $height && $block->hasSameTypeId(VanillaBlocks::AIR()) && ($blockBelow->hasSameTypeId(VanillaBlocks::GRASS()) || $blockBelow->hasSameTypeId(VanillaBlocks::DIRT()))) {
                $world->setBlockAt($x, $y, $z, $this->grassType);
                $succeeded = true;
            }
        }

        return $succeeded;
    }
}