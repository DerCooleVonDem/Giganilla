<?php

namespace JonasWindmann\Giganilla\generator\populator;

use JonasWindmann\Giganilla\GigaRandom;
use pocketmine\block\Block;
use pocketmine\block\VanillaBlocks;

class OreType {

    public Block $blockType;
    public int $minY;
    public int $maxY;
    public int $amount;
    public int $total;
    public Block $targetType;

    public function __construct(Block $blockType, int $minY, int $maxY, int $amount, int $total, ?Block $targetType = null) {
        $this->blockType = $blockType;
        $this->minY = $minY;
        $this->maxY = $maxY;
        $this->amount = $amount;
        $this->total = $total;
        $this->targetType = $targetType ?? VanillaBlocks::STONE();
    }

    public function getRandomHeight(GigaRandom $random): int {
        return $this->minY == $this->maxY ? $random->nextIntWithBound($this->minY) + $random->nextIntWithBound($this->minY) : $random->nextIntWithBound($this->maxY - $this->minY) + $this->minY;
    }
}