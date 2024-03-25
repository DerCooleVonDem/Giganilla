<?php

namespace JonasWindmann\Giganilla\generator\populator\decorator\objects;

use JonasWindmann\Giganilla\GigaRandom;
use pocketmine\block\Block;
use pocketmine\block\VanillaBlocks;
use pocketmine\world\BlockTransaction;
use pocketmine\world\ChunkManager;

class AcaciaTree extends GenericTree {


    public function initialize(GigaRandom $random, BlockTransaction $txn): void
    {
        parent::initialize($random, $txn);

        $this->height = $random->nextIntWithBound(3) + $random->nextIntWithBound(3) + 5;
        $this->setType(GenericTree::MAGIC_NUMBER_ACACIA);
    }

    public function canPlaceOn(Block $soil): bool {
        return $soil->hasSameTypeId(VanillaBlocks::GRASS()) || $soil->hasSameTypeId(VanillaBlocks::DIRT());
    }

    private function setLeaves(int $x, int $y, int $z, ChunkManager $world) {
        if ($world->getBlockAt($x, $y, $z)->hasSameTypeId(VanillaBlocks::AIR())) {
            $this->transaction->addBlockAt($x, $y, $z, $this->leavesTypes);
        }
    }

    public function generate(ChunkManager $world, GigaRandom $random, int $sourceX, int $sourceY, int $sourceZ): bool {
        if ($this->cannotGenerateAt($sourceX, $sourceY, $sourceZ, $world)) {
            return false;
        }

        $d = (float) ($random->nextFloat() * M_PI * 2.0); // random direction
        $dx = (int) (cos($d) + 1.5) - 1;
        $dz = (int) (sin($d) + 1.5) - 1;
        if (abs($dx) > 0 && abs($dz) > 0) { // reduce possible directions to NESW
            if ($random->nextInt()) {
                $dx = 0;
            } else {
                $dz = 0;
            }
        }

        $twistHeight = $this->height - 1 - $random->nextIntWithBound(4);
        $twistCount = $random->nextIntWithBound(3) + 1;
        $centerX = $sourceX;
        $centerZ = $sourceZ;
        $trunkTopY = 0;

        // generates the trunk
        for ($y = 0; $y < $this->height; $y++) {
            // trunk twists
            if ($twistCount > 0 && $y >= $twistHeight) {
                $centerX += $dx;
                $centerZ += $dz;
                $twistCount--;
            }

            $material = $world->getBlockAt($centerX, $sourceY + $y, $centerZ);
            if ($material->hasSameTypeId(VanillaBlocks::AIR()) || $material->hasSameTypeId(VanillaBlocks::BIRCH_LEAVES())) {
                $trunkTopY = $sourceY + $y;
                $this->transaction->addBlockAt($centerX, $sourceY + $y, $centerZ, $this->logType);
            }
        }

        // generates leaves
        for ($x = -3; $x <= 3; $x++) {
            for ($z = -3; $z <= 3; $z++) {
                if (abs($x) < 3 || abs($z) < 3) {
                    $this->setLeaves($centerX + $x, $trunkTopY, $centerZ + $z, $world);
                }
                if (abs($x) < 2 && abs($z) < 2) {
                    $this->setLeaves($centerX + $x, $trunkTopY + 1, $centerZ + $z, $world);
                }
                if (abs($x) == 2 && abs($z) == 0 || abs($x) == 0 && abs($z) == 2) {
                    $this->setLeaves($centerX + $x, $trunkTopY + 1, $centerZ + $z, $world);
                }
            }
        }

        // try to choose a different direction for second branching and canopy
        $d = (float) ($random->nextFloat() * M_PI * 2.0);
        $dxB = (int) (cos($d) + 1.5) - 1;
        $dzB = (int) (sin($d) + 1.5) - 1;
        if (abs($dxB) > 0 && abs($dzB) > 0) {
            if ($random->nextBoolean()) {
                $dxB = 0;
            } else {
                $dzB = 0;
            }
        }

        if ($dx != $dxB || $dz != $dzB) {
            $centerX = $sourceX;
            $centerZ = $sourceZ;
            $branchHeight = $twistHeight - 1 - $random->nextIntWithBound(2);
            $twistCount = $random->nextIntWithBound(3) + 1;
            $trunkTopY = 0;

            // generates the trunk
            for ($y = $branchHeight + 1; $y < $this->height; $y++) {
                if ($twistCount > 0) {
                    $centerX += $dxB;
                    $centerZ += $dzB;
                    $material = $world->getBlockAt($centerX, $sourceY + $y, $centerZ);
                    if ($material->hasSameTypeId(VanillaBlocks::AIR()) || $material->hasSameTypeId(VanillaBlocks::BIRCH_LEAVES())) {
                        $trunkTopY = $sourceY + $y;
                        $this->transaction->addBlockAt($centerX, $sourceY + $y, $centerZ, $this->logType);
                    }
                    $twistCount--;
                }
            }

            // generates the leaves
            if ($trunkTopY > 0) {
                for ($x = -2; $x <= 2; $x++) {
                    for ($z = -2; $z <= 2; $z++) {
                        if (abs($x) < 2 || abs($z) < 2) {
                            $this->setLeaves($centerX + $x, $trunkTopY, $centerZ + $z, $world);
                        }
                    }
                }
                for ($x = -1; $x <= 1; $x++) {
                    for ($z = -1; $z <= 1; $z++) {
                        $this->setLeaves($centerX + $x, $trunkTopY + 1, $centerZ + $z, $world);
                    }
                }
            }
        }

        return true;
    }
}
