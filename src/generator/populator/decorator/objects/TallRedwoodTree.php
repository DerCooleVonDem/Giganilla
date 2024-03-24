<?php

namespace JonasWindmann\Giganilla\generator\populator\decorator\objects;

use JonasWindmann\Giganilla\GigaRandom;
use pocketmine\block\VanillaBlocks;
use pocketmine\world\BlockTransaction;
use pocketmine\world\ChunkManager;

class TallRedwoodTree extends RedwoodTree {
    public function initialize(GigaRandom $random, BlockTransaction $txn): void {
        parent::initialize($random, $txn);

        $this->setOverrides([
            VanillaBlocks::AIR()->getTypeId(),
            VanillaBlocks::BIRCH_LEAVES()->getTypeId(),
            VanillaBlocks::ACACIA_LEAVES()->getTypeId(),
            VanillaBlocks::GRASS()->getTypeId(),
            VanillaBlocks::DIRT()->getTypeId(),
            VanillaBlocks::BIRCH_LOG()->getTypeId(),
            VanillaBlocks::ACACIA_LOG()->getTypeId(),
            VanillaBlocks::ACACIA_SAPLING()->getTypeId(),
            VanillaBlocks::VINES()->getTypeId()
        ]);
        $this->setHeight($random->nextIntWithBound(5) + 7);
        $this->setLeavesHeight($this->height - $random->nextIntWithBound(2) - 3);
        $this->setMaxRadius($random->nextIntWithBound($this->height - $this->leavesHeight + 1) + 1);
    }

    public function generate(ChunkManager $world, GigaRandom $random, int $sourceX, int $sourceY, int $sourceZ): bool {
        if ($this->cannotGenerateAt($sourceX, $sourceY, $sourceZ, $world)) {
            return false;
        }

        // generate the leaves
        $radius = 0;
        for ($y = $sourceY + $this->height; $y >= $sourceY + $this->leavesHeight; $y--) {
            // leaves are built from top to bottom
            for ($x = $sourceX - $radius; $x <= $sourceX + $radius; $x++) {
                for ($z = $sourceZ - $radius; $z <= $sourceZ + $radius; $z++) {
                    if ((abs($x - $sourceX) !== $radius || abs($z - $sourceZ) !== $radius || $radius <= 0) && $world->getBlockAt($x, $y, $z)->hasSameTypeId(VanillaBlocks::AIR())) {
                        $this->transaction->addBlockAt($x, $y, $z, $this->leavesTypes);
                    }
                }
            }
            if ($radius >= 1 && $y === $sourceY + $this->leavesHeight + 1) {
                $radius--;
            } elseif ($radius < $this->maxRadius) {
                $radius++;
            }
        }

        // generate the trunk
        for ($y = 0; $y < $this->height - 1; $y++) {
            $this->replaceIfAirOrLeaves($sourceX, $sourceY + $y, $sourceZ, $this->logType, $world);
        }

        // block below trunk is always dirt
        $this->transaction->addBlockAt($sourceX, $sourceY - 1, $sourceZ, VanillaBlocks::DIRT());

        return true;
    }
}