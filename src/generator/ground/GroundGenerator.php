<?php

namespace JonasWindmann\Giganilla\generator\ground;

use JonasWindmann\Giganilla\biome\BiomeClimate;
use JonasWindmann\Giganilla\GigaRandom;
use pocketmine\block\Block;
use pocketmine\block\VanillaBlocks;
use pocketmine\world\ChunkManager;

class GroundGenerator {
    // Assuming topMaterial and groundMaterial are properties of GroundGenerator
    public Block $topMaterial;
    public Block $groundMaterial;

    public function GenerateTerrainColumn(ChunkManager $world, GigaRandom $random, int $x, int $z, int $biome, float $surfaceNoise): void
    {
        $seaLevel = 64;

        $topMat = $this->topMaterial->GetStateId();
        $groundMat = $this->groundMaterial->GetStateId();

        $chunkX = $x;
        $chunkZ = $z;

        $surfaceHeight = max(floor($surfaceNoise / 3.0 + 3.0 + $random->NextFloat() * 0.25), 1);
        $deep = -1;

        $air = VanillaBlocks::AIR()->getStateId();
        $stone = VanillaBlocks::STONE()->getStateId();
        $sandstone = VanillaBlocks::SANDSTONE()->getStateId();
        $gravel = VanillaBlocks::GRAVEL()->getStateId();
        $bedrock = VanillaBlocks::BEDROCK()->getStateId();
        $ice = VanillaBlocks::ICE()->getStateId();
        $sand = VanillaBlocks::SAND()->getStateId();
        $stillWater = VanillaBlocks::WATER()->getStillForm()->getStateId();

        $chunk = $world->GetChunk($x >> 4, $z >> 4);
        $blockX = $x & 0x0f;
        $blockZ = $z & 0x0f;

        for ($y = 255; $y >= 0; --$y) {
            if ($y <= $random->nextIntWithBound(5)) {
                $chunk->setBlockStateId($blockX, $y, $blockZ, $bedrock);
            } else {
                $mat_id = $chunk->getBlockStateId($blockX, $y, $blockZ);
                if ($mat_id == $air) {
                    $deep = -1;
                } elseif ($mat_id == $stone) {
                    if ($deep == -1) {
                        if ($y >= $seaLevel - 5 && $y <= $seaLevel) {
                            $topMat = $this->topMaterial->GetStateId();
                            $groundMat = $this->groundMaterial->GetStateId();
                        }

                        $deep = $surfaceHeight;
                        if ($y >= $seaLevel - 2) {
                            $chunk->setBlockStateId($blockX, $y, $blockZ, $topMat);
                        } elseif ($y < $seaLevel - 8 - $surfaceHeight) {
                            $topMat = $air;
                            $groundMat = $stone;
                            $chunk->setBlockStateId($blockX, $y, $blockZ, $gravel);
                        } else {
                            $chunk->setBlockStateId($blockX, $y, $blockZ, $groundMat);
                        }
                    } elseif ($deep > 0) {
                        --$deep;
                        $chunk->setBlockStateId($blockX, $y, $blockZ, $groundMat);

                        if ($deep == 0 && $groundMat == $sand) {
                            $deep = $random->NextIntWithBound(4) + max(0, $y - $seaLevel - 1);
                            $groundMat = $sandstone;
                        }
                    }
                } elseif ($mat_id == $stillWater && $y == $seaLevel - 2 && BiomeClimate::getInstance()->IsCold($biome, $chunkX, $y, $chunkZ)) {
                    $chunk->setBlockStateId($blockX, $y, $blockZ, $ice);
                }
            }
        }
    }
}
