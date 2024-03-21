<?php

namespace JonasWindmann\Giganilla\generator\populator\decorator;

use JonasWindmann\Giganilla\GigaRandom;
use pocketmine\block\utils\DirtType;
use pocketmine\block\VanillaBlocks;
use pocketmine\world\ChunkManager;

class MushroomDecorator extends Decorator {
    private $block;
    private $density = 0.0;
    private $fixedHeightRange = false;

    public function __construct($block) {
        $this->block = $block;
    }

    public function setUseFixedHeightRange(): void {
        $this->fixedHeightRange = true;
    }

    public function setDensity(float $density): void {
        $this->density = $density;
    }

    public function decorate(ChunkManager $world, GigaRandom $random, int $chunkX, int $chunkZ): void {
        if ($random->nextFloat() < $this->density) {
            $chunk = $world->getChunk($chunkX, $chunkZ);

            $sourceX = ($chunkX << 4) + $random->nextIntWithBound(16);
            $sourceZ = ($chunkZ << 4) + $random->nextIntWithBound(16);
            $sourceY = $chunk->getHighestBlockAt($sourceX & 0x0f, $sourceZ & 0x0f);

            $sourceY = $this->fixedHeightRange ? $sourceY : $random->nextIntWithBound($sourceY << 1);

            for ($i = 0; $i < 64; $i++) {
                $x = $sourceX + $random->nextIntWithBound(8) - $random->nextIntWithBound(8);
                $z = $sourceZ + $random->nextIntWithBound(8) - $random->nextIntWithBound(8);
                $y = $sourceY + $random->nextIntWithBound(4) - $random->nextIntWithBound(4);

                $block = $world->getBlockAt($x, $y, $z);
                $blockBelow = $world->getBlockAt($x, $y - 1, $z);
                if ($y < 255 && $block->hasSameTypeId(VanillaBlocks::AIR())) {
                    $canPlaceShroom = false;
                    switch ($blockBelow->getTypeId()) {
                        // dont like how we are using the type id directly
                        case VanillaBlocks::PODZOL()->getTypeId():
                        case VanillaBlocks::MYCELIUM()->getTypeId():
                            $canPlaceShroom = true;
                            break;
                        case VanillaBlocks::GRASS()->getTypeId():
                            $canPlaceShroom = $block->getLightLevel() < 13;
                            break;
                        case VanillaBlocks::DIRT()->getTypeId():
                            if ($blockBelow->isSameState(VanillaBlocks::DIRT()->setDirtType(DirtType::COARSE))) { // Check for coarse dirt, bc it can be used as a base for mushrooms in certain light levels
                                $canPlaceShroom = $block->getLightLevel() < 13;
                            }
                            // useless else statement removed
                            break;
                    }

                    if ($canPlaceShroom) $world->setBlockAt($x, $y, $z, $this->block);
                }
            }
        }
    }
}