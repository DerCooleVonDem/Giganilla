<?php

namespace JonasWindmann\Giganilla\generator\populator\decorator\objects;

use JonasWindmann\Giganilla\GigaRandom;
use pocketmine\world\BlockTransaction;
use pocketmine\world\ChunkManager;

class MegaRedwoodTree extends MegaJungleTree {
    protected int $leavesHeight = 0;
    public function initialize(GigaRandom $random, BlockTransaction $txn): void {
        parent::initialize($random, $txn);
        $this->setHeight($random->nextIntWithBound(15) + $random->nextIntWithBound(3) + 13);
        $this->setType(GenericTree::MAGIC_NUMBER_SPRUCE);
        $this->setLeavesHeight($random->nextIntWithBound(5) + ($random->nextBoolean() ? 3 : 13));
    }

    public function setLeavesHeight(int $iLeavesHeight): void {
        $this->leavesHeight = $iLeavesHeight;
    }

    public function generate(ChunkManager $world, GigaRandom $random, int $sourceX, int $sourceY, int $sourceZ): bool {
        if ($this->cannotGenerateAt($sourceX, $sourceY, $sourceZ, $world)) {
            return false;
        }

        $previousRadius = 0;
        for ($y = $sourceY + $this->height - $this->leavesHeight; $y <= $sourceY + $this->height; $y++) {
            $n = $sourceY + $this->height - $y;
            $radius = (int) floor($n / $this->leavesHeight * 3.5);
            if ($radius === $previousRadius && $n > 0 && $y % 2 === 0) {
                $radius++;
            }
            $this->generateLeaves($sourceX, $y, $sourceZ, $radius, false, $world);
            $previousRadius = $radius;
        }

        $this->generateTrunk($world, $sourceX, $sourceY, $sourceZ);
        $this->generateDirtBelowTrunk($sourceX, $sourceY, $sourceZ);

        return true;
    }

    public function generateDirtBelowTrunk(int $blockX, int $blockY, int $blockZ): void {
        // NOOP: MegaRedwoodTree does not replace blocks below (surely to preserve podzol)
    }
}