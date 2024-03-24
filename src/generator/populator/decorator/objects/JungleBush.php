<?php

namespace JonasWindmann\Giganilla\generator\populator\decorator\objects;

use JonasWindmann\Giganilla\GigaRandom;
use pocketmine\block\Block;
use pocketmine\block\VanillaBlocks;
use pocketmine\world\BlockTransaction;
use pocketmine\world\ChunkManager;

class JungleBush extends GenericTree {
    public function initialize(GigaRandom $random, BlockTransaction $txn): void {
        parent::initialize($random, $txn);

        $this->setType(GenericTree::MAGIC_NUMBER_JUNGLE);
    }

    public function canPlaceOn(Block $soil): bool {
        return $soil->hasSameTypeId(VanillaBlocks::GRASS()) || $soil->hasSameTypeId(VanillaBlocks::DIRT());
    }

    public function generate(ChunkManager $world, GigaRandom $random, int $sourceX, int $sourceY, int $sourceZ): bool {
        $block = $world->getBlockAt($sourceX, $sourceY, $sourceZ);
        while (($block->hasSameTypeId(VanillaBlocks::AIR()) || $block->hasSameTypeId(VanillaBlocks::OAK_LEAVES())) && $sourceY > 0) {
            --$sourceY;
            $block = $world->getBlockAt($sourceX, $sourceY, $sourceZ)->getTypeId();
        }

        // check only below block
        if (!$this->canPlaceOn($world->getBlockAt($sourceX, $sourceY - 1, $sourceZ))) {
            return false;
        }

        // generates the trunk
        $adjustY = $sourceY;
        $this->transaction->addBlockAt($sourceX, $adjustY + 1, $sourceZ, $this->logType);

        // generates the leaves
        for ($y = $adjustY + 1; $y <= $adjustY + 3; ++$y) {
            $radius = 3 - ($y - $adjustY);

            for ($x = $sourceX - $radius; $x <= $sourceX + $radius; ++$x) {
                for ($z = $sourceZ - $radius; $z <= $sourceZ + $radius; ++$z) {
                    if (!$this->transaction->fetchBlockAt($x, $y, $z)->isSolid() &&
                        (abs($x - $sourceX) !== $radius || abs($z - $sourceZ) !== $radius || $random->nextBoolean())) {
                        $this->transaction->addBlockAt($x, $y, $z, $this->leavesTypes);
                    }
                }
            }
        }

        return true;
    }
}
