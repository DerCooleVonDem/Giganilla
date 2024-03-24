<?php

namespace JonasWindmann\Giganilla\generator\populator\decorator\objects;

use JonasWindmann\Giganilla\GigaRandom;
use pocketmine\block\VanillaBlocks;
use pocketmine\world\BlockTransaction;
use pocketmine\world\ChunkManager;

class RedwoodTree extends GenericTree {
    protected int $maxRadius = 0;
    protected int $leavesHeight = 0;

    public function initialize(GigaRandom $random, BlockTransaction $txn): void {
        parent::initialize($random, $txn);

        $this->setOverrides([VanillaBlocks::AIR()->getTypeId(), VanillaBlocks::JUNGLE_LEAVES()->getTypeId()]);
        $this->setHeight($random->nextIntWithBound(4) + 6);
        $this->setLeavesHeight($random->nextIntWithBound(2) + 1);
        $this->setMaxRadius($random->nextIntWithBound(2) + 2);
        $this->setType(GenericTree::MAGIC_NUMBER_SPRUCE);
    }

    public function setMaxRadius(int $maxRadius): void {
        $this->maxRadius = $maxRadius;
    }

    public function setLeavesHeight(int $leavesHeight): void {
        $this->leavesHeight = $leavesHeight;
    }

    public function canPlace(int $baseX, int $baseY, int $baseZ, ChunkManager $world): bool {
        for ($y = $baseY; $y <= $baseY + 1 + $this->height; $y++) {
            // Space requirement
            $radius = $y - $baseY < $this->leavesHeight ? 0 : $this->maxRadius;
            // check for block collision on horizontal slices
            for ($x = $baseX - $radius; $x <= $baseX + $radius; $x++) {
                for ($z = $baseZ - $radius; $z <= $baseZ + $radius; $z++) {
                    if ($y >= 0 && $y < 256) {
                        // we can overlap some blocks around
                        if (!in_array($world->getBlockAt($x, $y, $z)->getTypeId(), $this->overrides)) {
                            return false;
                        }
                    } else { // height out of range
                        return false;
                    }
                }
            }
        }

        return true;
    }

    public function generate(ChunkManager $world, GigaRandom $random, int $sourceX, int $sourceY, int $sourceZ): bool {
        if ($world->getBlockAt($sourceX, $sourceY, $sourceZ)->hasSameTypeId(VanillaBlocks::GRASS()) || $this->cannotGenerateAt($sourceX, ++$sourceY, $sourceZ, $world)) {
            return false;
        }

        // generate the leaves
        $radius = $random->nextIntWithBound(2);
        $peakRadius = 1;
        $minRadius = 0;
        for ($y = $sourceY + $this->height; $y >= $sourceY + $this->leavesHeight; $y--) {
            // leaves are built from top to bottom
            for ($x = $sourceX - $radius; $x <= $sourceX + $radius; $x++) {
                for ($z = $sourceZ - $radius; $z <= $sourceZ + $radius; $z++) {
                    if ((abs($x - $sourceX) !== $radius || abs($z - $sourceZ) !== $radius || $radius <= 0) && $world->getBlockAt($x, $y, $z)->hasSameTypeId(VanillaBlocks::AIR())) {
                        $this->transaction->addBlockAt($x, $y, $z, $this->leavesTypes);
                    }
                }
            }
            if ($radius >= $peakRadius) {
                $radius = $minRadius;
                $minRadius = 1; // after the peak radius is reached once, the min radius increases
                $peakRadius++; // the peak radius increases each time it's reached
                if ($peakRadius > $this->maxRadius) {
                    $peakRadius = $this->maxRadius;
                }
            } else {
                $radius++;
            }
        }

        // generate the trunk
        for ($y = 0; $y < $this->height - $random->nextIntWithBound(3); $y++) {
            $type = $world->getBlockAt($sourceX, $sourceY + $y, $sourceZ);
            if (!in_array($type->getTypeId(), $this->overrides)) {
                $this->transaction->addBlockAt($sourceX, $sourceY + $y, $sourceZ, $this->logType);
            }
        }

        // block below trunk is always dirt
        $this->transaction->addBlockAt($sourceX, $sourceY - 1, $sourceZ, VanillaBlocks::DIRT());

        return true;
    }
}