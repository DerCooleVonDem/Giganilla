<?php

namespace JonasWindmann\Giganilla\generator\populator\decorator\objects;

use JonasWindmann\Giganilla\GigaRandom;
use pocketmine\block\Block;
use pocketmine\block\DoublePlant;
use pocketmine\block\VanillaBlocks;
use pocketmine\world\ChunkManager;

class DoubleTallPlant extends TerrainObjects {
    private DoublePlant $species;

    public function __construct($subtype) {
        $this->species = $subtype;
    }

    public function generate(ChunkManager $world, GigaRandom $random, int $sourceX, int $sourceY, int $sourceZ): bool {
        $placed = false;
        $height = $world->getMaxY();
        for ($i = 0; $i < 64; ++$i) {
            $x = $sourceX + $random->nextIntWithBound(8) - $random->nextIntWithBound(8);
            $z = $sourceZ + $random->nextIntWithBound(8) - $random->nextIntWithBound(8);
            $y = $sourceY + $random->nextIntWithBound(4) - $random->nextIntWithBound(4);

            $block = $world->getBlockAt($x, $y, $z);
            $topBlock = $world->getBlockAt($x, $y + 1, $z);
            $belowBlock = $world->getBlockAt($x, $y - 1, $z);
            if ($y < $height && $block->hasSameTypeId(VanillaBlocks::AIR()) && $topBlock->hasSameTypeId(VanillaBlocks::AIR()) && $belowBlock->hasSameTypeId(VanillaBlocks::GRASS())) {
                // The first bit is a boolean, indicates that the block is a top.
                $world->setBlockAt($x, $y, $z, $this->species);
                // TODO: I really dont know if it is the right way to set the bit flag
                $world->setBlockAt($x, $y + 1, $z, $this->species->setTop(true));
                $placed = true;
            }
        }

        return $placed;
    }
}