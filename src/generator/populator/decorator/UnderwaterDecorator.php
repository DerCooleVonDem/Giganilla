<?php

namespace JonasWindmann\Giganilla\generator\populator\decorator;

use JonasWindmann\Giganilla\generator\populator\decorator\objects\BlockPatch;
use JonasWindmann\Giganilla\GigaRandom;
use pocketmine\block\Block;
use pocketmine\block\VanillaBlocks;
use pocketmine\world\ChunkManager;

class UnderwaterDecorator extends Decorator {
    private Block $type;
    private int $horizontalRadius = 0;
    private int $verticalRadius = 0;
    private array $overridable = [];

    public function __construct(Block $type) {
        $this->type = $type;
    }

    public function setOverridableBlocks(array $fullBlockOverrides): void {
        $this->overridable = $fullBlockOverrides;
    }

    public function setRadii(int $iHorizontalRadius, int $iVerticalRadius): void {
        $this->horizontalRadius = $iHorizontalRadius;
        $this->verticalRadius = $iVerticalRadius;
    }

    // TODO: needs further testing (rwb1, rwb2, getHighestBlockAt)
    public function decorate(ChunkManager $world, GigaRandom $random, int $chunkX, int $chunkZ): void {
        $rwb1 = $random->nextIntWithBound(16);
        $rwb2 = $random->nextIntWithBound(16);
        $sourceX = ($chunkX << 4) + $rwb1;
        $sourceZ = ($chunkZ << 4) + $rwb2;
        $chunk = $world->getChunk($chunkX, $chunkZ);
        $sourceY = $chunk->getHighestBlockAt($rwb2, $rwb2) - 1;

        $block = $world->getBlockAt($sourceX, $sourceY - 1, $sourceZ);
        while ($sourceY > 1 && $block->hasSameTypeId(VanillaBlocks::WATER())) {
            --$sourceY;
        }

        $material = $world->getBlockAt($sourceX, $sourceY, $sourceZ);
        if ($material->hasSameTypeId(VanillaBlocks::WATER())) {
            (new BlockPatch($this->type, $this->horizontalRadius, $this->verticalRadius, $this->overridable))->generate($world, $random, $sourceX, $sourceY, $sourceZ);
        }
    }
}