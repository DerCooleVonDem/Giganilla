<?php

namespace JonasWindmann\Giganilla\generator\populator\decorator\objects;

use JonasWindmann\Giganilla\biome\BiomeClimate;
use JonasWindmann\Giganilla\biome\BiomeList;
use JonasWindmann\Giganilla\GigaRandom;
use pocketmine\block\Block;
use pocketmine\block\VanillaBlocks;
use pocketmine\world\BlockTransaction;
use pocketmine\world\ChunkManager;

class Lake extends TerrainObjects {

    const LAKE_MAX_HEIGHT = 8;
    const LAKE_MAX_DIAMETER = 16;

    private Block $type; // Filling block (water/lava)
    private BlockTransaction $transaction;

    public function __construct(Block $block, BlockTransaction $transaction) {
        $this->type = $block;
        $this->transaction = $transaction;
    }

    public function generate(ChunkManager $world, GigaRandom $random, int $sourceX, int $sourceY, int $sourceZ): bool {
        $sizeX = $sizeY = $sizeZ = $dx = $dy = $dz = 0.0; // Fancy right? Just noticed it whiles reading the docs lol
        $succeeded = false;
        $sourceY -= 4;

        $lakeMap = [];
        for ($n = 0; $n < $random->nextIntWithBound(4) + 4; ++$n) {
            $sizeX = $random->nextFloat() * 6.0 + 3;
            $sizeY = $random->nextFloat() * 4.0 + 2;
            $sizeZ = $random->nextFloat() * 6.0 + 3;
            $dx = $random->nextFloat() * (self::LAKE_MAX_DIAMETER - $sizeX - 2) + 1 + $sizeX / 2.0;
            $dy = $random->nextFloat() * (self::LAKE_MAX_HEIGHT - $sizeY - 4) + 2 + $sizeY / 2.0;
            $dz = $random->nextFloat() * (self::LAKE_MAX_DIAMETER - $sizeZ - 2) + 1 + $sizeZ / 2.0;

            for ($x = 1; $x < self::LAKE_MAX_DIAMETER - 1; ++$x) {
                for ($z = 1; $z < self::LAKE_MAX_DIAMETER - 1; ++$z) {
                    for ($y = 1; $y < self::LAKE_MAX_HEIGHT - 1; ++$y) {
                        $nx = pow(($x - $dx) / ($sizeX / 2.0), 2);
                        $ny = pow(($y - $dy) / ($sizeY / 2.0), 2);
                        $nz = pow(($z - $dz) / ($sizeZ / 2.0), 2);

                        if (($nx + $ny + $nz) < 1.0) {
                            $this->setLakeBlock($lakeMap, $x, $y, $z);
                            $succeeded = true;
                        }
                    }
                }
            }
        }

        if (!$this->canPlace($lakeMap, $world, $sourceX, $sourceY, $sourceZ)) return false;

        $chunk = $world->getChunk($sourceX >> 4, $sourceZ >> 4);

        // TODO: Check if this is the correct way to get the biomegrid, bc the biomegrid array is not in the chunk class and was moved to the subchunk class (vertical biomes possible...?)
        $subChunk = $chunk->getSubChunk($sourceY >> 4);
        $biomeArray = $subChunk->getBiomeArray();

        $biome = $biomeArray->get(($sourceX + 8 + self::LAKE_MAX_DIAMETER / 2) & 0x0f, ($sourceZ + 8 + self::LAKE_MAX_DIAMETER / 2) & 0x0f);
        $mycel_biome = $biome == BiomeList::MUSHROOM_SHORE;

        $stillWater = VanillaBlocks::WATER()->getStillForm();
        for ($x = 0; $x < self::LAKE_MAX_DIAMETER; ++$x) {
            for ($z = 0; $z < self::LAKE_MAX_DIAMETER; ++$z) {
                for ($y = 0; $y < self::LAKE_MAX_DIAMETER; ++$y) {
                    if (!$this->isLakeBlock($lakeMap, $x, $y, $z)) {
                        continue;
                    }

                    $replaceType = $this->type;
                    $block = $world->getBlockAt($sourceX + $x, $sourceY + $y, $sourceZ + $z);
                    $blockAbove = $world->getBlockAt($sourceX + $x, $sourceY + $y + 1, $sourceZ + $z);
                    if (($block->hasSameTypeId(VanillaBlocks::DIRT()) && ($blockAbove->hasSameTypeId(VanillaBlocks::OAK_LOG()) || $blockAbove->hasSameTypeId(VanillaBlocks::ACACIA_LOG()))) || $block->hasSameTypeId(VanillaBlocks::BIRCH_LOG())
                        || $block->hasSameTypeId(VanillaBlocks::ACACIA_LOG())) {
                        continue;
                    }

                    if ($y >= (self::LAKE_MAX_HEIGHT / 2)) {
                        $replaceType = VanillaBlocks::AIR();
                        if ($this->killWeakBlocksAbove($world, $sourceX + $x, $sourceY + $y, $sourceZ + $z)) {
                            break;
                        }

                        if (($block->hasSameTypeId(VanillaBlocks::ICE()) || $block->hasSameTypeId(VanillaBlocks::PACKED_ICE())) && $this->type->getStateId() == $stillWater->getStateId()) {
                            $replaceType = $block;
                        }
                    } elseif ($y == (self::LAKE_MAX_HEIGHT / 2 - 1)) {
                        $biome = $biomeArray->get($x & 0x0f, $z & 0x0f);

                        // StateId does make sence here because still water is a subform of the water block
                        //                   V                            V
                        if ($this->type->getStateId() == $stillWater->getStateId() && BiomeClimate::getInstance()->IsCold($biome, $sourceX + $x, $y, $sourceZ + $z)) {
                            $this->type = VanillaBlocks::ICE();
                        }
                    }

                    $this->transaction->addBlockAt($sourceX + $x, $sourceY + $y, $sourceZ + $z, $replaceType);
                }
            }
        }

        for ($x = 0; $x < self::LAKE_MAX_DIAMETER; ++$x) {
            for ($z = 0; $z < self::LAKE_MAX_DIAMETER; ++$z) {
                for ($y = self::LAKE_MAX_HEIGHT / 2; $y < self::LAKE_MAX_HEIGHT; ++$y) {
                    if (!$this->isLakeBlock($lakeMap, $x, $y, $z)) {
                        continue;
                    }

                    $block = $world->getBlockAt($sourceX + $x, $sourceY + $y - 1, $sourceZ + $z);
                    $blockAbove = $world->getBlockAt($sourceX + $x, $sourceY + $y, $sourceZ + $z);
                    if ($block->hasSameTypeId(VanillaBlocks::DIRT()) && $blockAbove->isTransparent() && $blockAbove->getLightLevel() > 0) {
                        $this->transaction->addBlockAt($sourceX + $x, $sourceY + $y - 1, $sourceZ + $z, $mycel_biome ? VanillaBlocks::MYCELIUM() : VanillaBlocks::GRASS());
                    }
                }
            }
        }

        return $succeeded;
    }

    private function isLakeBlock(array &$lakeMap, int $x, int $y, int $z): bool {
        return in_array((($x * self::LAKE_MAX_DIAMETER + $z) * self::LAKE_MAX_HEIGHT + $y), $lakeMap);
    }

    private function setLakeBlock(array &$lakeMap, int $x, int $y, int $z): void {
        $lakeMap[] = (($x * self::LAKE_MAX_DIAMETER + $z) * self::LAKE_MAX_HEIGHT + $y);
    }

    public function canPlace(array &$lakeMap, ChunkManager $world, int $sourceX, int $sourceY, int $sourceZ): bool {
        for ($x = 0; $x < self::LAKE_MAX_DIAMETER; ++$x) {
            for ($z = 0; $z < self::LAKE_MAX_DIAMETER; ++$z) {
                for ($y = 0; $y < self::LAKE_MAX_HEIGHT; ++$y) {

                    if ($this->isLakeBlock($lakeMap, $x, $y, $z)
                        || (($x >= (self::LAKE_MAX_DIAMETER - 1) || !$this->isLakeBlock($lakeMap, $x + 1, $y, $z))
                            && ($x <= 0 || !$this->isLakeBlock($lakeMap, $x - 1, $y, $z))
                            && ($z >= (self::LAKE_MAX_DIAMETER - 1) || !$this->isLakeBlock($lakeMap, $x, $y, $z + 1))
                            && ($z <= 0 || !$this->isLakeBlock($lakeMap, $x, $y, $z - 1))
                            && ($y >= (self::LAKE_MAX_HEIGHT - 1) || !$this->isLakeBlock($lakeMap, $x, $y + 1, $z))
                            && ($y <= 0 || !$this->isLakeBlock($lakeMap, $x, $y - 1, $z)))) {
                        continue;
                    }

                    $block = $world->getBlockAt($sourceX + $x, $sourceY + $y, $sourceZ + $z);

                    // TODO: Here was an isLiquid call but was replaced by two checks for water and lava (should work like in the other cases...?)
                    if ($y >= self::LAKE_MAX_HEIGHT / 2 && ($block->hasSameTypeId(VanillaBlocks::WATER()) || $block->hasSameTypeId(VanillaBlocks::LAVA()) || $block->hasSameTypeId(VanillaBlocks::ICE()))) {
                        return false; // there's already some liquids above
                    }

                    if ($y < self::LAKE_MAX_HEIGHT / 2 && !$block->isSolid() && $block->getTypeId() != $this->type->getTypeId()) {
                        return false; // bottom must be solid and do not overlap with another liquid type
                    }
                }
            }
        }

        return true;
    }
}