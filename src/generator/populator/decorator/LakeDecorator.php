<?php

namespace JonasWindmann\Giganilla\generator\populator\decorator;

use JonasWindmann\Giganilla\generator\populator\decorator\objects\Lake;
use JonasWindmann\Giganilla\GigaRandom;
use pocketmine\block\Block;
use pocketmine\block\VanillaBlocks;
use pocketmine\world\BlockTransaction;
use pocketmine\world\ChunkManager;

class LakeDecorator extends Decorator
{
    private Block $block;
    private int $rarity;
    private int $baseOffset = 0;

    public function __construct(Block $block, int $populatorRarity, int $offset = 0) {
        $this->block = $block;
        $this->rarity = $populatorRarity;
        $this->baseOffset = $offset;
    }

    public function decorate(ChunkManager $world, GigaRandom $random, int $chunkX, int $chunkZ): void {
        if ($random->nextIntWithBound($this->rarity) == 0) {
            $sourceX = ($chunkX << 4) + $random->nextIntWithBound(16);
            $sourceZ = ($chunkZ << 4) + $random->nextIntWithBound(16);
            $sourceY = $random->nextIntWithBound($world->getMaxY() - $this->baseOffset) + $this->baseOffset;
            if ($this->block->hasSameTypeId(VanillaBlocks::LAVA()) && ($sourceY >= 64 || $random->nextIntWithBound(10) > 0)) {
                return;
            }

            while ($world->getBlockAt($sourceX, $sourceY, $sourceZ)->hasSameTypeId(VanillaBlocks::AIR()) && $sourceY > 5) {
                --$sourceY;
            }

            if ($sourceY >= 5) {
                $txn = new BlockTransaction($world);
                $txn->addValidator(function(ChunkManager $manager, int $x, int $y, int $z): bool{
                    return !$manager->getBlockAt($x, $y, $z)->hasSameTypeId(VanillaBlocks::WATER());
                });

                $lake = new Lake($this->block, $txn);

                if ($lake->generate($world, $random, $sourceX, $sourceY, $sourceZ)) {
                    $txn->apply();
                }
            }
        }
    }
}