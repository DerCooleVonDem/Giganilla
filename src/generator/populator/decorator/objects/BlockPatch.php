<?php

namespace JonasWindmann\Giganilla\generator\populator\decorator\objects;

use JonasWindmann\Giganilla\generator\populator\decorator\objects\TerrainObjects;
use JonasWindmann\Giganilla\GigaRandom;
use pocketmine\block\Block;
use pocketmine\world\ChunkManager;

class BlockPatch {
    public $overrides_ = [];
    public Block $blockType;
    public int $horizontalRadius;
    public int $verticalRadius;

    public function __construct(Block $blockType, int $iHorizontalRadius, int $iVerticalRadius, array $vOverrides) {
        $this->blockType = $blockType;
        $this->horizontalRadius = $iHorizontalRadius;
        $this->verticalRadius = $iVerticalRadius;
        foreach ($vOverrides as $block) {
            $this->overrides_[] = $block->getStateId();
        }
    }

    public function generate(ChunkManager $world, GigaRandom $random, int $sourceX, int $sourceY, int $sourceZ): bool {
        $success = false;

        $n = $random->nextIntWithBound($this->horizontalRadius - 2) + 2;
        $nSquared = $n * $n;

        for ($x = $sourceX - $n; $x <= $sourceX + $n; $x++) {
            for ($z = $sourceZ - $n; $z <= $sourceZ + $n; $z++) {
                if (($x - $sourceX) * ($x - $sourceX) + ($z - $sourceZ) * ($z - $sourceZ) > $nSquared) {
                    continue;
                }

                for ($y = $sourceY - $this->verticalRadius; $y <= $sourceY + $this->verticalRadius; $y++) {

                    $block = $world->getBlockAt($x, $y, $z);
                    if (!in_array($block->getStateId(), $this->overrides_)) {
                        continue;
                    }

                    if (TerrainObjects::killWeakBlocksAbove($world, $x, $y, $z)) {
                        continue;
                    }

                    $world->setBlockAt($x, $y, $z, $this->blockType);
                    $success = true;
                    break;
                }
            }
        }

        return $success;
    }
}