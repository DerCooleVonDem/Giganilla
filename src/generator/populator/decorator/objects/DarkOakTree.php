<?php

namespace JonasWindmann\Giganilla\generator\populator\decorator\objects;

use JonasWindmann\Giganilla\GigaRandom;
use pocketmine\block\Block;
use pocketmine\block\VanillaBlocks;
use pocketmine\world\BlockTransaction;
use pocketmine\world\ChunkManager;

class DarkOakTree extends GenericTree {

    public function initialize(GigaRandom $random, BlockTransaction $txn): void
    {
        parent::initialize($random, $txn);

        $this->setOverrides([
            VanillaBlocks::AIR(),
            VanillaBlocks::BIRCH_LEAVES(),
            VanillaBlocks::ACACIA_LEAVES(),
            VanillaBlocks::GRASS(),
            VanillaBlocks::DIRT(),
            VanillaBlocks::BIRCH_LOG(),
            VanillaBlocks::ACACIA_LOG(),
            VanillaBlocks::ACACIA_SAPLING(),
            VanillaBlocks::VINES()
        ]);
        $this->setHeight($random->nextIntWithBound(2) + $random->nextIntWithBound(3) + 6);
        $this->setType(GenericTree::MAGIC_NUMBER_DARK_OAK);
    }

    public function canPlaceOn(Block $soil): bool
    {
        return $soil->hasSameTypeId(VanillaBlocks::GRASS()) || $soil->hasSameTypeId(VanillaBlocks::DIRT());
    }

    private function setLeaves(int $x, int $y, int $z, ChunkManager $world): void
    {
        if ($world->getBlockAt($x, $y, $z)->hasSameTypeId(VanillaBlocks::AIR())) {
            $this->transaction->addBlockAt($x, $y, $z, $this->leavesTypes);
        }
    }

    public function generate(ChunkManager $world, GigaRandom $random, int $sourceX, int $sourceY, int $sourceZ): bool
    {
        if ($this->cannotGenerateAt($sourceX, $sourceY, $sourceZ, $world)) {
            return false;
        }

        $d = $random->nextFloat() * M_PI * 2.0; // random direction
        $dx = (int) (cos($d) + 1.5) - 1;
        $dz = (int) (sin($d) + 1.5) - 1;
        if (abs($dx) > 0 && abs($dz) > 0) { // reduce possible directions to North, East, South, West
            if ($random->nextBoolean()) {
                $dx = 0;
            } else {
                $dz = 0;
            }
        }

        $twistHeight = $this->height - $random->nextIntWithBound(4);
        $twistCount = $random->nextIntWithBound(3);
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
            if ($material->hasSameTypeId(VanillaBlocks::AIR()) && $material->hasSameTypeId(VanillaBlocks::BIRCH_LEAVES())) {
                continue;
            }
            $trunkTopY = $sourceY + $y;
            // SELF, SOUTH, EAST, SOUTH EAST
            $this->transaction->addBlockAt($centerX, $sourceY + $y, $centerZ, $this->logType);
            $this->transaction->addBlockAt($centerX, $sourceY + $y, $centerZ + 1, $this->logType);
            $this->transaction->addBlockAt($centerX + 1, $sourceY + $y, $centerZ, $this->logType);
            $this->transaction->addBlockAt($centerX + 1, $sourceY + $y, $centerZ + 1, $this->logType);
        }

        // generates leaves
        for ($x = -2; $x <= 0; $x++) {
            for ($z = -2; $z <= 0; $z++) {
                if ((abs($x) != 1 || abs($z) != 2) && (abs($x) > 1 || abs($z) > 1)) {
                    $this->setLeaves($centerX + $x, $trunkTopY + 1, $centerZ + $z, $world);
                    $this->setLeaves(1 + $centerX - $x, $trunkTopY + 1, $centerZ + $z, $world);
                    $this->setLeaves($centerX + $x, $trunkTopY + 1, 1 + $centerZ - $z, $world);
                    $this->setLeaves(1 + $centerX - $x, $trunkTopY + 1, 1 + $centerZ - $z, $world);
                }
                $this->setLeaves($centerX + $x, $trunkTopY - 1, $centerZ + $z, $world);
                $this->setLeaves(1 + $centerX - $x, $trunkTopY - 1, $centerZ + $z, $world);
                $this->setLeaves($centerX + $x, $trunkTopY - 1, 1 + $centerZ - $z, $world);
                $this->setLeaves(1 + $centerX - $x, $trunkTopY - 1, 1 + $centerZ - $z, $world);
            }
        }

        // finish leaves below the canopy
        for ($x = -3; $x <= 4; $x++) {
            for ($z = -3; $z <= 4; $z++) {
                if (abs($x) < 3 || abs($z) < 3) {
                    $this->setLeaves($centerX + $x, $trunkTopY, $centerZ + $z, $world);
                }
            }
        }

        // generates some trunk excrescences
        for ($x = -1; $x <= 2; $x++) {
            for ($z = -1; $z <= 2; $z++) {
                if ((abs($x) != 1 || abs($z) != 1) && (abs($x) > 1 || abs($z) > 1) || $random->nextIntWithBound(3) != 0) {
                    continue;
                }
                for ($y = 0; $y < $random->nextIntWithBound(3) + 2; $y++) {
                    $material = $world->getBlockAt($sourceX + $x, $trunkTopY - $y - 1, $sourceZ + $z);
                    if ($material->hasSameTypeId(VanillaBlocks::AIR()) || $material->hasSameTypeId(VanillaBlocks::BIRCH_LEAVES())) {
                        $this->transaction->addBlockAt($sourceX + $x, $trunkTopY - $y - 1, $sourceZ + $z, $this->logType);
                    }
                }

                // leaves below the canopy
                for ($i = -1; $i <= 1; $i++) {
                    for ($j = -1; $j <= 1; $j++) {
                        $this->setLeaves($centerX + $x + $i, $trunkTopY, $centerZ + $z + $j, $world);
                    }
                }
                for ($i = -2; $i <= 2; $i++) {
                    for ($j = -2; $j <= 2; $j++) {
                        if (abs($i) < 2 || abs($j) < 2) {
                            $this->setLeaves($centerX + $x + $i, $trunkTopY - 1, $centerZ + $z + $j, $world);
                        }
                    }
                }
            }
        }

        // 50% chance to have a 4 leaves cap in the center of the canopy
        if ($random->nextIntWithBound(2) == 0) {
            $this->setLeaves($centerX, $trunkTopY + 2, $centerZ, $world);
            $this->setLeaves($centerX + 1, $trunkTopY + 2, $centerZ, $world);
            $this->setLeaves($centerX + 1, $trunkTopY + 2, $centerZ + 1, $world);
            $this->setLeaves($centerX, $trunkTopY + 2, $centerZ + 1, $world);
        }

        $dirt = VanillaBlocks::DIRT();

        // block below trunk is always dirt (SELF, SOUTH, EAST, SOUTH EAST)
        $this->transaction->addBlockAt($sourceX, $sourceY - 1, $sourceZ, $dirt);
        $this->transaction->addBlockAt($sourceX, $sourceY - 1, $sourceZ + 1, $dirt);
        $this->transaction->addBlockAt($sourceX + 1, $sourceY - 1, $sourceZ, $dirt);
        $this->transaction->addBlockAt($sourceX + 1, $sourceY - 1, $sourceZ + 1, $dirt);

        return true;
    }
}
