<?php

namespace JonasWindmann\Giganilla\generator\populator\decorator\objects;


use JonasWindmann\Giganilla\GigaRandom;
use pocketmine\block\Block;
use pocketmine\block\VanillaBlocks;
use pocketmine\world\BlockTransaction;
use pocketmine\world\ChunkManager;

class BrownMushroomTree extends GenericTree {
    protected Block $type;

    public function initialize(GigaRandom $random, BlockTransaction $txn): void
    {
        parent::initialize($random, $txn);

        $this->setOverrides([VanillaBlocks::AIR()->getTypeId(), VanillaBlocks::JUNGLE_LEAVES()->getTypeId(), VanillaBlocks::ACACIA_LEAVES()->getTypeId()]);
        $this->setHeight($random->nextIntWithBound(3) + 4);
    }

    public function canPlaceOn(Block $soil): bool
    {
        return $soil->hasSameTypeId(VanillaBlocks::GRASS()) || $soil->hasSameTypeId(VanillaBlocks::DIRT()) || $soil->hasSameTypeId(VanillaBlocks::MYCELIUM());
    }

    public function generate(ChunkManager $world, GigaRandom $random, int $sourceX, int $sourceY, int $sourceZ): bool
    {
        if ($this->cannotGenerateAt($sourceX, $sourceY, $sourceZ, $world)) {
            return false;
        }

        // generate the stem
        for ($y = 0; $y < $this->height; $y++) {
            $this->transaction->addBlockAt($sourceX, $sourceY + $y, $sourceZ, VanillaBlocks::MUSHROOM_STEM()); // stem texture
        }

        // TODO: 1.13, replace with MultipleFacing BlockData
        // get the mushroom's cap Y start
        $capY = $sourceY + $this->height; // for brown mushroom it starts on top directly
        if ($this->type->hasSameTypeId(VanillaBlocks::RED_MUSHROOM_BLOCK())) {
            $capY = $sourceY + $this->height - 3; // for red mushroom, cap's thickness is 4 blocks
        }

        // generate mushroom's cap
        for ($y = $capY; $y <= $sourceY + $this->height; $y++) { // from bottom to top of mushroom
            $radius = 1; // radius for the top of red mushroom
            if ($y < $sourceY + $this->height) {
                $radius = 2; // radius for red mushroom cap is 2
            }
            if ($this->type->hasSameTypeId(VanillaBlocks::BROWN_MUSHROOM_BLOCK())) {
                $radius = 3; // radius always 3 for a brown mushroom
            }

            // loop over horizontal slice
            for ($x = $sourceX - $radius; $x <= $sourceX + $radius; $x++) {
                for ($z = $sourceZ - $radius; $z <= $sourceZ + $radius; $z++) {
                    $data = 5; // cap texture on top
                    // cap's borders/corners treatment
                    if ($x == $sourceX - $radius) {
                        $data = 4; // cap texture on top and west
                    } else if ($x == $sourceX + $radius) {
                        $data = 6; // cap texture on top and east
                    }
                    if ($z == $sourceZ - $radius) {
                        $data -= 3;
                    } else if ($z == $sourceZ + $radius) {
                        $data += 3;
                    }

                    // corners shrink treatment
                    // if it's a brown mushroom we need it always
                    // it's a red mushroom, it's only applied below the top
                    if ($this->type->hasSameTypeId(VanillaBlocks::BROWN_MUSHROOM_BLOCK()) || $y < $sourceY + $this->height) {
                        // excludes the real corners of the cap structure
                        if (($x == $sourceX - $radius || $x == $sourceX + $radius) && ($z == $sourceZ - $radius || $z == $sourceZ + $radius)) {
                            continue;
                        }

                        // mushroom's cap corners treatment
                        // TODO: Implement corner treatment logic
                    }

                    // a data of 5 below the top layer means air
                    if ($data != 5 || $y >= $sourceY + $this->height) {
                        // TODO: 1.13, set BlockData
                        $this->transaction->addBlockAt($x, $y, $z, $this->type);
                    }
                }
            }
        }

        return true;
    }

    public function canPlace(int $baseX, int $baseY, int $baseZ, ChunkManager $world): bool
    {
        for ($y = $baseY; $y <= $baseY + 1 + $this->height; $y++) {
            $radius = 3;
            if ($y <= $baseY + 3) {
                $radius = 0; // radius is 0 below 4 blocks tall (only the stem to take in account)
            }

            // check for block collision on horizontal slices
            for ($x = $baseX - $radius; $x <= $baseX + $radius; $x++) {
                for ($z = $baseZ - $radius; $z <= $baseZ + $radius; $z++) {
                    if ($y < 0 || $y >= 256) { // height out of range
                        return false;
                    }
                    // skip source block check
                    if ($y != $baseY || $x != $baseX || $z != $baseZ) {
                        // we can overlap leaves around
                        $blockType = $world->getBlockAt($x, $y, $z);
                        if (!in_array($blockType->getTypeId(), $this->overrides)) {
                            return false;
                        }
                    }
                }
            }
        }
        return true;
    }
}
