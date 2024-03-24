<?php

namespace JonasWindmann\Giganilla\generator\populator\decorator;
use JonasWindmann\Giganilla\GigaRandom;
use pocketmine\block\VanillaBlocks;
use pocketmine\world\ChunkManager;
use pocketmine\world\World;

class WaterLilyDecorator extends Decorator {
    public function decorate(ChunkManager $world, GigaRandom $random, int $chunkX, int $chunkZ): void {
        $chunk = $world->getChunk($chunkX, $chunkZ);
        $xr = $random->nextIntWithBound(16);
        $zr = $random->nextIntWithBound(16);
        $sourceX = ($chunkX << 4) + $xr;
        $sourceZ = ($chunkZ << 4) + $zr;
        $sourceY = $random->nextIntWithBound($chunk->getHighestBlockAt($xr, $zr) << 1);
        while ($world->getBlockAt($sourceX, $sourceY - 1, $sourceZ)->hasSameTypeId(VanillaBlocks::AIR()) && $sourceY > 0) {
            --$sourceY;
        }

        for ($j = 0; $j < 10; ++$j) {
            $x = $sourceX + $random->nextIntWithBound(8) - $random->nextIntWithBound(8);
            $z = $sourceZ + $random->nextIntWithBound(8) - $random->nextIntWithBound(8);
            $y = $sourceY + $random->nextIntWithBound(4) - $random->nextIntWithBound(4);

            if ($y >= 0 && $y < World::Y_MAX && $world->getBlockAt($x, $y, $z)->hasSameTypeId(VanillaBlocks::AIR()) && $world->getBlockAt($x, $y - 1, $z)->hasSameTypeId(VanillaBlocks::WATER())) {
                $world->setBlockAt($x, $y, $z, VanillaBlocks::LILY_PAD());
            }
        }
    }
}
