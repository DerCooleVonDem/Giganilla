<?php

namespace JonasWindmann\Giganilla\generator\populator\decorator\objects;

use JonasWindmann\Giganilla\GigaRandom;
use pocketmine\block\Block;
use pocketmine\block\VanillaBlocks;
use pocketmine\world\BlockTransaction;
use pocketmine\world\ChunkManager;
use pocketmine\world\World;

class GenericTree extends TerrainObjects {
    private int $height = 0;
    protected BlockTransaction $transaction;
    protected array $overrides = [];
    protected Block $logType;
    protected Block $leavesTypes;

    public function __construct()
    {
        $this->logType = VanillaBlocks::OAK_LOG();
        $this->leavesTypes = VanillaBlocks::OAK_LEAVES();
    }

    public function generate(ChunkManager $world, GigaRandom $random, int $sourceX, int $sourceY, int $sourceZ): bool
    {
        if (!$this->cannotGenerateAt($sourceX, $sourceY, $sourceZ, $world)) {
            return false;
        }

        // Generate the leaves
        for ($y = $sourceY + $this->height - 3; $y <= $sourceY + $this->height; ++$y) {
            $n = $y - ($sourceY + $this->height);
            $radius = 1 - $n / 2;

            for ($x = $sourceX - $radius; $x <= $sourceX + $radius; ++$x) {
                for ($z = $sourceZ - $radius; $z <= $sourceZ + $radius; ++$z) {
                    if (abs($x - $sourceX) != $radius || abs($z - $sourceZ) != $radius || ($random->nextBoolean() && $n != 0)) {
                        $this->replaceIfAirOrLeaves($x, $y, $z, $this->leavesTypes, $world);
                    }
                }
            }
        }

        // Generate the trunk
        for ($y = 0; $y < $this->height; ++$y) {
            $this->replaceIfAirOrLeaves($sourceX, $sourceY + $y, $sourceZ, $this->logType, $world);
        }

        // Block below trunk is always dirt
        $this->transaction->addBlockAt($sourceX, $sourceY - 1, $sourceZ, VanillaBlocks::DIRT());

        return true;
    }

    public function canHeightFit(int $baseHeight): bool
    {
        return $baseHeight >= 1 && $baseHeight + $this->height + 1 < World::Y_MAX;
    }

    public function canPlace(int $baseX, int $baseY, int $baseZ, ChunkManager $world): bool
    {
        for ($y = $baseY; $y <= $baseY + 1 + $this->height; ++$y) {
            $radius = $y == $baseY ? 0 : ($y >= $baseY + 1 + $this->height - 2 ? 2 : 1);

            for ($x = $baseX - $radius; $x <= $baseX + $radius; ++$x) {
                for ($z = $baseZ - $radius; $z <= $baseZ + $radius; ++$z) {
                    if ($y >= 0 && $y < $world->getMaxY()) {
                        if (!in_array($world->getBlockAt($x, $y, $z)->getTypeId(), $this->overrides)) {
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

    public function replaceIfAirOrLeaves(int $x, int $y, int $z, Block $newBlock, ChunkManager $world): void
    {
        $oldBlock = $world->getBlockAt($x, $y, $z);
        if ($oldBlock->hasSameTypeId(VanillaBlocks::AIR()) || in_array($oldBlock->getTypeId(), [VanillaBlocks::ACACIA_LEAVES()->getTypeId(), VanillaBlocks::BIRCH_LEAVES()->getTypeId(), VanillaBlocks::DARK_OAK_LEAVES()->getTypeId(), VanillaBlocks::JUNGLE_LEAVES()->getTypeId(), VanillaBlocks::OAK_LEAVES()->getTypeId(), VanillaBlocks::SPRUCE_LEAVES()->getTypeId()])) {
            $this->transaction->addBlockAt($x, $y, $z, $newBlock);
        }
    }

    public function canPlaceOn(Block $soil): bool
    {
        return $soil->hasSameTypeId(VanillaBlocks::GRASS()) || $soil->hasSameTypeId(VanillaBlocks::DIRT()) || $soil->hasSameTypeId(VanillaBlocks::FARMLAND());
    }

    public function cannotGenerateAt(int $baseX, int $baseY, int $baseZ, ChunkManager $world): bool
    {
        return !$this->canHeightFit($baseY) || !$this->canPlaceOn($world->getBlockAt($baseX, $baseY - 1, $baseZ)) || !$this->canPlace($baseX, $baseY, $baseZ, $world);
    }

    public function initialize(GigaRandom $random, BlockTransaction $txn): void
    {
        $this->height = $random->nextIntWithBound(3) + 4;
        $this->transaction = $txn;
    }

    public function setHeight(int $blockHeight): void
    {
        $this->height = $blockHeight;
    }

    public function setOverrides(array $overridable): void
    {
        $this->overrides = $overridable;
    }

    public function setType(int $magicNumber): void
    {
        switch ($magicNumber) {
            case 0:
                // Oak
                $this->logType = VanillaBlocks::OAK_LOG();
                $this->leavesTypes = VanillaBlocks::OAK_LEAVES();
                break;
            case 1:
                // Spruce
                $this->logType = VanillaBlocks::SPRUCE_LOG();
                $this->leavesTypes = VanillaBlocks::SPRUCE_LEAVES();
                break;
            case 2:
                // Birch
                $this->logType = VanillaBlocks::BIRCH_LOG();
                $this->leavesTypes = VanillaBlocks::BIRCH_LEAVES();
                break;
            case 3:
                // Jungle
                $this->logType = VanillaBlocks::JUNGLE_LOG();
                $this->leavesTypes = VanillaBlocks::JUNGLE_LEAVES();
                break;
            case 4:
                // Acacia
                $this->logType = VanillaBlocks::ACACIA_LOG();
                $this->leavesTypes = VanillaBlocks::ACACIA_LEAVES();
                break;
            case 5:
                // Dark Oak
                $this->logType = VanillaBlocks::DARK_OAK_LOG();
                $this->leavesTypes = VanillaBlocks::DARK_OAK_LEAVES();
                break;
        }
    }

    // TODO: This thing had params (references) in the original code and i cannot explain why they are there atm
    public static function OakTree(): self
    {
        return new self();
    }
}