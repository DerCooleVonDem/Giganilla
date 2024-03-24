<?php

namespace JonasWindmann\Giganilla\generator\populator\decorator\objects;

use JonasWindmann\Giganilla\GigaRandom;
use pocketmine\block\Block;
use pocketmine\block\VanillaBlocks;
use pocketmine\math\Facing;
use pocketmine\world\BlockTransaction;
use pocketmine\world\ChunkManager;

class MegaJungleTree extends GenericTree {
    public function initialize(GigaRandom $random, BlockTransaction $txn): void {
        parent::initialize($random, $txn);

        $this->setHeight($random->nextIntWithBound(20) + $random->nextIntWithBound(3) + 10);
        $this->setType(GenericTree::MAGIC_NUMBER_JUNGLE);
    }

    public function canPlaceOn(Block $soil): bool {
        return $soil->hasSameTypeId(VanillaBlocks::GRASS()) || $soil->hasSameTypeId(VanillaBlocks::DIRT());
    }

    public function canPlace(int $baseX, int $baseY, int $baseZ, ChunkManager $world): bool {
        for ($y = $baseY; $y <= $baseY + 1 + $this->height; $y++) {
            $radius = 2;
            if ($y === $baseY) {
                $radius = 1;
            } elseif ($y >= $baseY + 1 + $this->height - 2) {
                $radius = 2;
            }
            for ($x = $baseX - $radius; $x <= $baseX + $radius; $x++) {
                for ($z = $baseZ - $radius; $z <= $baseZ + $radius; $z++) {
                    if ($y >= 0 && $y < 256) {
                        $blockType = $world->getBlockAt($x, $y, $z);
                        if (!in_array($blockType->getTypeId(), $this->overrides)) {
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
        if ($this->cannotGenerateAt($sourceX, $sourceY, $sourceZ, $world)) {
            return false;
        }

        // generates the canopy leaves
        for ($y = -2; $y <= 0; $y++) {
            $this->generateLeaves($sourceX, $sourceY + $this->height + $y, $sourceZ, 3 - $y, false, $world);
        }

        // generates the branches
        $branchHeight = $this->height - 2 - $random->nextIntWithBound(4);
        while ($branchHeight > $this->height / 2) {
            $x = 0;
            $z = 0;
            $d = $random->nextFloat() * M_PI * 2.0;
            for ($i = 0; $i < 5; $i++) {
                $x = (int)(cos($d) * $i + 1.5);
                $z = (int)(sin($d) * $i + 1.5);
                $this->transaction->addBlockAt($sourceX + $x, $sourceY + $branchHeight - 3 + $i / 2, $sourceZ + $z, $this->logType);
            }
            for ($y = $branchHeight - $random->nextIntWithBound(2) + 1; $y <= $branchHeight; $y++) {
                $this->generateLeaves($sourceX + $x, $sourceY + $y, $sourceZ + $z, 1 - ($y - $branchHeight), true, $world);
            }
            $branchHeight -= $random->nextIntWithBound(4) + 2;
        }

        // generates the trunk
        $this->generateTrunk($world, $sourceX, $sourceY, $sourceZ);

        // add some vines on the trunk
        $this->addVinesOnTrunk($world, $sourceX, $sourceY, $sourceZ, $random);

        // blocks below trunk are always dirt
        $this->generateDirtBelowTrunk($sourceX, $sourceY, $sourceZ);

        return true;
    }

    protected function generateLeaves(int $sourceX, int $sourceY, int $sourceZ, int $radius, bool $odd, ChunkManager $world): void {
        $n = $odd ? 0 : 1;
        for ($x = $sourceX - $radius; $x <= $sourceX + $radius + $n; $x++) {
            $radiusX = $x - $sourceX;
            for ($z = $sourceZ - $radius; $z <= $sourceZ + $radius + $n; $z++) {
                $radiusZ = $z - $sourceZ;

                $sqX = $radiusX * $radiusX;
                $sqZ = $radiusZ * $radiusZ;
                $sqR = $radius * $radius;
                $sqXb = ($radiusX - $n) * ($radiusX - $n);
                $sqZb = ($radiusZ - $n) * ($radiusZ - $n);

                if ($sqX + $sqZ <= $sqR || $sqXb + $sqZb <= $sqR || $sqX + $sqZb <= $sqR || $sqXb + $sqZ <= $sqR) {
                    $this->replaceIfAirOrLeaves($x, $sourceY, $z, $this->leavesTypes, $world);
                }
            }
        }
    }

    protected function generateTrunk(ChunkManager $world, int $blockX, int $blockY, int $blockZ): void {
        for ($y = 0; $y < $this->height + 1; $y++) {
            $this->transaction->addBlockAt($blockX, $blockY + $y, $blockZ, $this->logType);
            $this->transaction->addBlockAt($blockX, $blockY + $y, $blockZ + 1, $this->logType);
            $this->transaction->addBlockAt($blockX + 1, $blockY + $y, $blockZ, $this->logType);
            $this->transaction->addBlockAt($blockX + 1, $blockY + $y, $blockZ + 1, $this->logType);
        }
    }

    private function generateDirtBelowTrunk(int $blockX, int $blockY, int $blockZ): void {
        $dirtBlock = VanillaBlocks::DIRT();

        $this->transaction->addBlockAt($blockX, $blockY - 1, $blockZ, $dirtBlock);
        $this->transaction->addBlockAt($blockX, $blockY - 1, $blockZ + 1, $dirtBlock);
        $this->transaction->addBlockAt($blockX + 1, $blockY - 1, $blockZ, $dirtBlock);
        $this->transaction->addBlockAt($blockX + 1, $blockY - 1, $blockZ + 1, $dirtBlock);
    }

    private function addVinesOnTrunk(ChunkManager $world, int $blockX, int $blockY, int $blockZ, GigaRandom $random): void {
        for ($y = 1; $y < $this->height; $y++) {
            $this->maybePlaceVine($world, $blockX - 1, $blockY + $y, $blockZ, Facing::EAST, $random);
            $this->maybePlaceVine($world, $blockX, $blockY + $y, $blockZ - 1, Facing::SOUTH, $random);
            $this->maybePlaceVine($world, $blockX + 2, $blockY + $y, $blockZ, Facing::WEST, $random);
            $this->maybePlaceVine($world, $blockX + 1, $blockY + $y, $blockZ - 1, Facing::SOUTH, $random);
            $this->maybePlaceVine($world, $blockX + 2, $blockY + $y, $blockZ + 1, Facing::WEST, $random);
            $this->maybePlaceVine($world, $blockX + 1, $blockY + $y, $blockZ + 2, Facing::NORTH, $random);
            $this->maybePlaceVine($world, $blockX - 1, $blockY + $y, $blockZ + 1, Facing::EAST, $random);
            $this->maybePlaceVine($world, $blockX, $blockY + $y, $blockZ + 2, Facing::NORTH, $random);
        }
    }

    private function maybePlaceVine(ChunkManager $world, int $absoluteX, int $absoluteY, int $absoluteZ, int $direction, GigaRandom $random): void {
        if ($random->nextIntWithBound(3) !== 0 && $world->getBlockAt($absoluteX, $absoluteY, $absoluteZ)->hasSameTypeId(VanillaBlocks::AIR())) {
            $vineBlock = VanillaBlocks::VINES()->setFace($direction, true);

            $this->transaction->addBlockAt($absoluteX, $absoluteY, $absoluteZ, $vineBlock);
        }
    }

    public function replaceIfAirOrLeaves(int $x, int $y, int $z, Block|array $newBlock, ChunkManager $world): void {
        $block = $world->getBlockAt($x, $y, $z);
        if ($block->hasSameTypeId(VanillaBlocks::AIR()) || in_array($block->getTypeId(), $newBlock)) {
            $this->transaction->addBlockAt($x, $y, $z, $this->leavesTypes);
        }
    }
}
