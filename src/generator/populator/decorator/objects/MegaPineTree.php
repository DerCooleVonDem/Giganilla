<?php

namespace JonasWindmann\Giganilla\generator\populator\decorator\objects;

use JonasWindmann\Giganilla\GigaRandom;
use pocketmine\block\VanillaBlocks;
use pocketmine\world\BlockTransaction;
use pocketmine\world\ChunkManager;

class MegaPineTree extends MegaRedwoodTree {

    private GigaRandom $random;

    public function initialize(GigaRandom $random, BlockTransaction $txn): void {
        parent::initialize($random, $txn);
        $this->setLeavesHeight($random->nextIntWithBound(5) + 3);
        $this->random = $random;
    }

    public function generate(ChunkManager $world, GigaRandom $random, int $sourceX, int $sourceY, int $sourceZ): bool {
        $generated = parent::generate($world, $this->random, $sourceX, $sourceY, $sourceZ);
        if ($generated) {
            $this->generatePodzol($sourceX, $sourceY, $sourceZ, $world, $this->random);
        }
        return $generated;
    }

    public function generateDirtBelowTrunk(int $blockX, int $blockY, int $blockZ): void {
        $podzolBlock = VanillaBlocks::PODZOL();
        $this->transaction->addBlockAt($blockX, $blockY - 1, $blockZ, $podzolBlock);
        $this->transaction->addBlockAt($blockX, $blockY - 1, $blockZ + 1, $podzolBlock);
        $this->transaction->addBlockAt($blockX + 1, $blockY - 1, $blockZ, $podzolBlock);
        $this->transaction->addBlockAt($blockX + 1, $blockY - 1, $blockZ + 1, $podzolBlock);
    }

    private function generatePodzol(int $sourceX, int $sourceY, int $sourceZ, ChunkManager $world, GigaRandom $random): void {
        $this->generatePodzolPatch($sourceX - 1, $sourceY, $sourceZ - 1, $world);
        $this->generatePodzolPatch($sourceX + 2, $sourceY, $sourceZ - 1, $world);
        $this->generatePodzolPatch($sourceX - 1, $sourceY, $sourceZ + 2, $world);
        $this->generatePodzolPatch($sourceX + 2, $sourceY, $sourceZ + 2, $world);
        for ($i = 0; $i < 5; $i++) {
            $n = $random->nextIntWithBound(64);
            if ($n % 8 === 0 || $n % 8 === 7 || $n / 8 === 0 || $n / 8 === 7) {
                $this->generatePodzolPatch($sourceX - 3 + $n % 8, $sourceY, $sourceZ - 3 + $n / 8, $world);
            }
        }
    }

    private function generatePodzolPatch(int $sourceX, int $sourceY, int $sourceZ, ChunkManager $world): void {
        for ($x = -2; $x <= 2; $x++) {
            for ($z = -2; $z <= 2; $z++) {
                if ($x === 2 && $z === 2) {
                    continue;
                }
                for ($y = 2; $y >= -3; $y--) {
                    $block = $world->getBlockAt($sourceX + $x, $sourceY + $y, $sourceZ + $z);
                    if ($block->hasSameTypeId(VanillaBlocks::GRASS()) || $block->hasSameTypeId(VanillaBlocks::DIRT())) {
                        $dirt = VanillaBlocks::DIRT();
                        if (!$world->getBlockAt($sourceX + $x, $sourceY + $y + 1, $sourceZ + $z)->isSolid()) {
                            $dirt = VanillaBlocks::PODZOL();
                        }
                        $world->setBlockAt($sourceX + $x, $sourceY + $y, $sourceZ + $z, $dirt);
                    } elseif (!$block->hasSameTypeId(VanillaBlocks::AIR()) && $sourceY + $y < $sourceY) {
                        break;
                    }
                }
            }
        }
    }
}