<?php

namespace JonasWindmann\Giganilla\generator\populator\decorator\objects;

use JonasWindmann\Giganilla\GigaRandom;
use pocketmine\block\Block;
use pocketmine\block\VanillaBlocks;
use pocketmine\world\BlockTransaction;
use pocketmine\world\ChunkManager;

class SwampTree extends CocoaTree {

    public function initialize(GigaRandom $random, BlockTransaction $txn): void {
        parent::initialize($random, $txn);

        $this->setOverrides([VanillaBlocks::AIR()->getTypeId(), VanillaBlocks::JUNGLE_LEAVES()->getTypeId()]);
        $this->setHeight($random->nextIntWithBound(4) + 5);
        $this->setType(GenericTree::MAGIC_NUMBER_OAK);
    }

    public function canPlaceOn(Block $soil): bool {
        return $soil->hasSameTypeId(VanillaBlocks::GRASS()) || $soil->hasSameTypeId(VanillaBlocks::DIRT());
    }

    public function canPlace(int $baseX, int $baseY, int $baseZ, ChunkManager $world): bool {
        for ($y = $baseY; $y <= $baseY + 1 + $this->height; $y++) {
            if ($y < 0 || $y >= 256) { // height out of range
                return false;
            }
            // Space requirement
            $radius = 1; // default radius if above first block
            if ($y === $baseY) {
                $radius = 0; // radius at source block y is 0 (only trunk)
            } elseif ($y >= $baseY + 1 + $this->height - 2) {
                $radius = 3; // max radius starting at leaves bottom
            }
            // check for block collision on horizontal slices
            for ($x = $baseX - $radius; $x <= $baseX + $radius; $x++) {
                for ($z = $baseZ - $radius; $z <= $baseZ + $radius; $z++) {
                    // we can overlap some blocks around
                    $blockType = $world->getBlockAt($x, $y, $z);
                    if (in_array($blockType->getTypeId(), $this->overrides)) {
                        continue;
                    }
                    // the trunk can be immersed by 1 block of water
                    if ($blockType->hasSameTypeId(VanillaBlocks::WATER())) {
                        if ($y > $baseY) {
                            return false;
                        }
                    } else {
                        return false;
                    }
                }
            }
        }
        return true;
    }

    public function generate(ChunkManager $world, GigaRandom $random, int $sourceX, int $sourceY, int $sourceZ): bool {
        while ($world->getBlockAt($sourceX, $sourceY, $sourceZ)->hasSameTypeId(VanillaBlocks::WATER())) {
            $sourceY--;
        }

        $sourceY++;
        if ($this->cannotGenerateAt($sourceX, $sourceY, $sourceZ, $world)) {
            return false;
        }

        // generate the leaves
        for ($y = $sourceY + $this->height - 3; $y <= $sourceY + $this->height; $y++) {
            $n = $y - ($sourceY + $this->height);
            $radius = 2 - $n / 2;
            for ($x = $sourceX - $radius; $x <= $sourceX + $radius; $x++) {
                for ($z = $sourceZ - $radius; $z <= $sourceZ + $radius; $z++) {
                    if (abs($x - $sourceX) !== $radius || abs($z - $sourceZ) !== $radius || $random->nextBoolean() && $n !== 0) {
                        $this->replaceIfAirOrLeaves($x, $y, $z, $this->leavesTypes, $world);
                    }
                }
            }
        }

        // generate the trunk
        for ($y = 0; $y < $this->height; $y++) {
            $material = $world->getBlockAt($sourceX, $sourceY + $y, $sourceZ);
            if ($material->hasSameTypeId(VanillaBlocks::AIR()) || $material->hasSameTypeId(VanillaBlocks::JUNGLE_LEAVES()) || $material->hasSameTypeId(VanillaBlocks::WATER())) {
                $this->transaction->addBlockAt($sourceX, $sourceY + $y, $sourceZ, $this->logType);
            }
        }

        // add some vines on the leaves
        $this->addVinesOnLeaves($sourceX, $sourceY, $sourceZ, $world, $random);

        // block below trunk is always dirt
        $this->transaction->addBlockAt($sourceX, $sourceY - 1, $sourceZ, VanillaBlocks::DIRT());

        return true;
    }
}
