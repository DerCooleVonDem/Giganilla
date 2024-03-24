<?php

namespace JonasWindmann\Giganilla\generator\populator\decorator\objects;

use JonasWindmann\Giganilla\GigaRandom;
use pocketmine\block\VanillaBlocks;
use pocketmine\math\Facing;
use pocketmine\math\Vector3;
use pocketmine\world\ChunkManager;

// TODO: Finish this up
class CocoaTree extends JungleTree {
    const SIZE_SMALL = 0;
    const SIZE_MEDIUM = 1;
    const SIZE_LARGE = 2;

    const COCOA_FACES = [Facing::NORTH, Facing::EAST, Facing::SOUTH, Facing::WEST];
    const COCOA_SIZE = [self::SIZE_SMALL, self::SIZE_MEDIUM, self::SIZE_LARGE];

    public function generate(ChunkManager $world, GigaRandom $random, int $sourceX, int $sourceY, int $sourceZ): bool {
        if (!parent::generate($world, $random, $sourceX, $sourceY, $sourceZ)) {
            return false;
        }

        $this->addVinesOnTrunk($sourceX, $sourceY, $sourceZ, $world, $random);
        $this->addVinesOnLeaves($sourceX, $sourceY, $sourceZ, $world, $random);
        $this->addCocoa($sourceX, $sourceY, $sourceZ, $world, $random);

        return true;
    }

    protected function addVinesOnTrunk(int $trunkX, int $trunkY, int $trunkZ, ChunkManager $world, GigaRandom $random): void {
        for ($y = 1; $y < $this->height; $y++) {
            if ($random->nextIntWithBound(3) !== 0 && $world->getBlockAt($trunkX - 1, $trunkY + $y, $trunkZ)->hasSameTypeId(VanillaBlocks::AIR())) {
                // Implementation of adding vines on the trunk.
            }
            // Similar checks for other directions.
        }
    }

    protected function addHangingVine(int $x, int $y, int $z, int $facing, ChunkManager $world): void {
        for ($i = 0; $i < 5; $i++) {
            if (!$world->getBlockAt($x, $y - $i, $z)->hasSameTypeId(VanillaBlocks::AIR())) {
                break;
            }
            // Implementation of adding hanging vines.
        }
    }

    protected function addVinesOnLeaves(int $baseX, int $baseY, int $baseZ, ChunkManager $world, GigaRandom $random): void {
        for ($y = $baseY - 3 + $this->height; $y <= $baseY + $this->height; $y++) {
            $ny = $y - ($baseY + $this->height);
            $radius = 2 - $ny / 2;
            for ($x = $baseX - $radius; $x <= $baseX + $radius; $x++) {
                for ($z = $baseZ - $radius; $z <= $baseZ + $radius; $z++) {
                    if ($world->getBlockAt($x, $y, $z)->hasSameTypeId(VanillaBlocks::BIRCH_LEAVES())) {
                        // Implementation of adding vines on leaves.
                    }
                }
            }
        }
    }

    protected function addCocoa(int $sourceX, int $sourceY, int $sourceZ, ChunkManager $world, GigaRandom $random): void {
        if ($this->height > 5 && $random->nextIntWithBound(5) === 0) {
            for ($y = 0; $y < 2; $y++) {
                foreach (self::COCOA_FACES as $cocoaFace) {
                    if ($random->nextIntWithBound(4 - $y) === 0) {
                        $size = self::COCOA_SIZE[$random->nextIntWithBound(2)];
                        $block = (new Vector3($sourceX, $sourceY, $sourceZ))->getSide($cocoaFace);

                        if (!$world->getBlockAt($block->getFloorX(), $block->getFloorY(), $block->getFloorZ())->hasSameTypeId(VanillaBlocks::AIR())) {
                            continue;
                        }
                        // Implementation of adding cocoa.
                    }
                }
            }
        }
    }
}