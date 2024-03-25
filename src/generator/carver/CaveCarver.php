<?php

namespace JonasWindmann\Giganilla\generator\carver;

use JonasWindmann\Giganilla\GigaRandom;
use pocketmine\block\RuntimeBlockStateRegistry;
use pocketmine\block\VanillaBlocks;
use pocketmine\math\Facing;
use pocketmine\math\Vector3;
use pocketmine\world\ChunkManager;
use pocketmine\world\format\Chunk;

class CaveCarver
{
    const CHUNK_RADIUS = 8;
    const DENSITY = 16;
    const TOP_Y = 128;
    const BOTTOM_Y = 8;
    const CAVE_LIQUID_ALTITUDE = 10;

    private GigaRandom $rand;

    public function __construct()
    {
        $this->rand = new GigaRandom(0);
    }

    public function generate(ChunkManager $manager, GigaRandom $random, int $chunkX, int $chunkZ, Chunk $chunk)
    {
        $this->rand->setSeed($random->getSeed());

        $j = $this->rand->nextLong();
        $k = $this->rand->nextLong();
        for ($currChunkX = $chunkX - self::CHUNK_RADIUS; $currChunkX <= $chunkX + self::CHUNK_RADIUS; ++$currChunkX) {
            for ($currChunkZ = $chunkZ - self::CHUNK_RADIUS; $currChunkZ <= $chunkZ + self::CHUNK_RADIUS; ++$currChunkZ) {
                $j1 = $currChunkX * $j;
                $k1 = $currChunkZ * $k;

                $this->rand->setSeed((int)$j1 ^ (int)$k1 ^ $random->getSeed());

                $this->recursiveGenerate($currChunkX, $currChunkZ, $chunkX, $chunkZ, $chunk, true);
            }
        }
    }

    private function recursiveGenerate(int $chunkX, int $chunkZ, int $originalChunkX, int $originalChunkZ, Chunk $chunk, bool $addRooms)
    {
        $numAttempts = $this->rand->nextIntWithBound($this->rand->nextIntWithBound($this->rand->nextIntWithBound(15) + 1) + 1);

        if ($this->rand->nextIntWithBound(100) > self::DENSITY) {
            $numAttempts = 0;
        }

        for ($i = 0; $i < $numAttempts; ++$i) {
            $caveStartX = $chunkX * 16 + $this->rand->nextIntWithBound(16);
            $caveStartY = $this->rand->nextIntWithBound(self::TOP_Y - self::BOTTOM_Y) + self::BOTTOM_Y;
            $caveStartZ = $chunkZ * 16 + $this->rand->nextIntWithBound(16);

            $numAddTunnelCalls = 1;

            if ($addRooms && $this->rand->nextIntWithBound(4) == 0) {
                $this->addRoom($this->rand->nextLong(), $originalChunkX, $originalChunkZ, $chunk, $caveStartX, $caveStartY, $caveStartZ);
                $numAddTunnelCalls += $this->rand->nextIntWithBound(4);
            }

            for ($j = 0; $j < $numAddTunnelCalls; ++$j) {
                $yaw = $this->rand->nextFloat() * (M_PI * 2);
                $pitch = ($this->rand->nextFloat() - 0.5) * 2.0 / 8.0;
                $width = $this->rand->nextFloat() * 2.0 + $this->rand->nextFloat();

                if ($addRooms && $this->rand->nextIntWithBound(10) == 0) {
                    $width *= $this->rand->nextFloat() * $this->rand->nextFloat() * 3.0 + 1.0;
                }

                $this->addTunnel($this->rand->nextLong(), $originalChunkX, $originalChunkZ, $chunk, $caveStartX, $caveStartY, $caveStartZ, $width, $yaw, $pitch, 0, 0, 1.0);
            }
        }
    }

    private function addRoom($seed, $originChunkX, $originChunkZ, $chunk, $caveStartX, $caveStartY, $caveStartZ)
    {
        $this->addTunnel($seed, $originChunkX, $originChunkZ, $chunk, $caveStartX, $caveStartY, $caveStartZ, 1.0 + $this->rand->nextFloat() * 6.0, 0.0, 0.0, -1, -1, 0.5);
    }

    private function addTunnel($seed, $originChunkX, $originChunkZ, $chunk, $caveStartX, $caveStartY, $caveStartZ, $width, $yaw, $pitch, $startCounter, $endCounter, $heightModifier) {
        $random = new GigaRandom($seed);

        $originBlockX = ($originChunkX * 16 + 8);
        $originBlockZ = ($originChunkZ * 16 + 8);

        $yawModifier = 0.0;
        $pitchModifier = 0.0;

        if ($endCounter <= 0) {
            $i = self::CHUNK_RADIUS * 16 - 16;
            $endCounter = $i - $random->nextIntWithBound($i / 4);
        }

        $comesFromRoom = false;

        if ($startCounter == -1) {
            $startCounter = $endCounter / 2;
            $comesFromRoom = true;
        }

        $randomCounterValue = $random->nextIntWithBound($endCounter / 2) + $endCounter / 4;

        while ($startCounter < $endCounter) {
            $xzOffset = 1.5 + sin($startCounter * M_PI / $endCounter) * $width;
            $yOffset = $xzOffset * $heightModifier;

            $pitchXZ = cos($pitch);
            $pitchY = sin($pitch);
            $caveStartX += cos($yaw) * $pitchXZ;
            $caveStartY += $pitchY;
            $caveStartZ += sin($yaw) * $pitchXZ;

            $flag = $random->nextIntWithBound(6) == 0;
            if ($flag) {
                $pitch = $pitch * 0.92;
            } else {
                $pitch = $pitch * 0.7;
            }

            $pitch = $pitch + $pitchModifier * 0.1;
            $yaw += $yawModifier * 0.1;

            $pitchModifier = $pitchModifier * 0.9;
            $yawModifier = $yawModifier * 0.75;

            $pitchModifier = $pitchModifier + ($random->nextFloat() - $random->nextFloat()) * $random->nextFloat() * 2.0;
            $yawModifier = $yawModifier + ($random->nextFloat() - $random->nextFloat()) * $random->nextFloat() * 4.0;

            if (!$comesFromRoom && $startCounter == $randomCounterValue && $width > 1.0 && $endCounter > 0) {
                $this->addTunnel($random->nextLong(), $originChunkX, $originChunkZ, $chunk, $caveStartX, $caveStartY, $caveStartZ, $random->nextFloat() * 0.5 + 0.5, $yaw - (M_PI / 2.0), $pitch / 3.0, $startCounter, $endCounter, 1.0);
                $this->addTunnel($random->nextLong(), $originChunkX, $originChunkZ, $chunk, $caveStartX, $caveStartY, $caveStartZ, $random->nextFloat() * 0.5 + 0.5, $yaw + (M_PI / 2.0), $pitch / 3.0, $startCounter, $endCounter, 1.0);
                return;
            }

            if ($comesFromRoom || $random->nextInt(4) != 0) {
                $caveStartXOffsetFromCenter = $caveStartX - $originBlockX;
                $caveStartZOffsetFromCenter = $caveStartZ - $originBlockZ;
                $distanceToEnd = $endCounter - $startCounter;
                $d7 = $width + 2.0 + 16.0;

                if ($caveStartXOffsetFromCenter * $caveStartXOffsetFromCenter + $caveStartZOffsetFromCenter * $caveStartZOffsetFromCenter - $distanceToEnd * $distanceToEnd > $d7 * $d7) {
                    return;
                }

                if ($caveStartX >= $originBlockX - 16.0 - $xzOffset * 2.0 && $caveStartZ >= $originBlockZ - 16.0 - $xzOffset * 2.0 && $caveStartX <= $originBlockX + 16.0 + $xzOffset * 2.0 && $caveStartZ <= $originBlockZ + 16.0 + $xzOffset * 2.0) {
                    $minX = floor($caveStartX - $xzOffset) - $originChunkX * 16 - 1;
                    $minY = floor($caveStartY - $yOffset) - 1;
                    $minZ = floor($caveStartZ - $xzOffset) - $originChunkZ * 16 - 1;
                    $maxX = floor($caveStartX + $xzOffset) - $originChunkX * 16 + 1;
                    $maxY = floor($caveStartY + $yOffset) + 1;
                    $maxZ = floor($caveStartZ + $xzOffset) - $originChunkZ * 16 + 1;

                    if ($minX < 0)   $minX = 0;
                    if ($maxX > 16) $maxX = 16;
                    if ($minY < 1)   $minY = 1;
                    if ($maxY > 248) $maxY = 248;
                    if ($minZ < 0)   $minZ = 0;
                    if ($maxZ > 16) $maxZ = 16;

                    for ($currX = $minX; $currX < $maxX; ++$currX) {
                        $xAxisDist = (($currX + $originChunkX * 16) + 0.5 - $caveStartX) / $xzOffset;

                        for ($currZ = $minZ; $currZ < $maxZ; ++$currZ) {
                            $zAxisDist = (($currZ + $originChunkZ * 16) + 0.5 - $caveStartZ) / $xzOffset;

                            if ($xAxisDist * $xAxisDist + $zAxisDist * $zAxisDist < 1.0) {
                                for ($currY = $maxY; $currY > $minY; --$currY) {
                                    $yAxisDist = (($currY - 1) + 0.5 - $caveStartY) / $yOffset;

                                    if ($yAxisDist > -0.7 && $xAxisDist * $xAxisDist + $yAxisDist * $yAxisDist + $zAxisDist * $zAxisDist < 1.0) {
                                        $this->digBlock($chunk, $currX, $currY, $currZ);
                                    }
                                }
                            }
                        }
                    }

                    if ($comesFromRoom) {
                        break;
                    }
                }
            }
            $startCounter++;
        }
    }

    private function digBlock(Chunk $chunk, int $currX, int $currY, int $currZ) {
        $stillLava = VanillaBlocks::LAVA()->getStillForm()->GetStateId();
        $airBlock = VanillaBlocks::AIR()->GetStateId();
        $grassBlock = VanillaBlocks::GRASS()->GetStateId();

        if ($this->canReplaceBlock($chunk, $currX, $currY, $currZ)) {
            if ($currY - 1 < self::CAVE_LIQUID_ALTITUDE) {
                $chunk->setBlockStateId($currX, $currY, $currZ, $stillLava);
            } else {
                $chunk->setBlockStateId($currX, $currY, $currZ, $airBlock);

                $healY = $currY - 1;

                $stateId = $chunk->getBlockStateId($currX, $healY, $currZ);
                // TODO: Not as great bc its adding another lookup, need to find a way to avoid this.
                $block = RuntimeBlockStateRegistry::getInstance()->fromStateId($stateId);

                if ($block->hasSameTypeId(VanillaBlocks::DIRT()) && $chunk->GetHighestBlockAt($currX, $currZ) == $healY) {
                    $chunk->setBlockStateId($currX, $healY, $currZ, $grassBlock);
                }
            }
        }
    }

    private function canReplaceBlock(Chunk $chunk, int $currX, int $currY, int $currZ) {
        // TODO: Not as great bc its adding another lookup, need to find a way to avoid this.
        $block = RuntimeBlockStateRegistry::getInstance()->fromStateId($chunk->getBlockStateId($currX, $currY, $currZ));
        $blockAbove = RuntimeBlockStateRegistry::getInstance()->fromStateId($chunk->getBlockStateId($currX, $currY, $currZ));

        // Avoid damaging trees and digging out under trees.
        if ($block->hasSameTypeId(VanillaBlocks::JUNGLE_LOG()) || $block->hasSameTypeId(VanillaBlocks::JUNGLE_LEAVES()) || $block->hasSameTypeId(VanillaBlocks::ACACIA_LEAVES()) || $block->hasSameTypeId(VanillaBlocks::DARK_OAK_LOG()) || $blockAbove->hasSameTypeId(VanillaBlocks::JUNGLE_LOG()) || $blockAbove->hasSameTypeId(VanillaBlocks::ACACIA_LOG())) {
            return false;
        }

        if ($currY > self::CAVE_LIQUID_ALTITUDE) {
            $unsafeCopy = new Vector3($currX, $currY, $currZ);
            foreach (Facing::ALL as $facing) {
                $facingSide = $unsafeCopy->getSide($facing);

                if ($facingSide->GetFloorX() >= 0 && $facingSide->GetFloorX() <= 15 && $facingSide->GetFloorZ() >= 0 && $facingSide->GetFloorZ() <= 15) {
                    $blockFace = RuntimeBlockStateRegistry::getInstance()->fromStateId($chunk->getBlockStateId($facingSide->GetFloorX(), $facingSide->GetFloorY(), $facingSide->GetFloorZ()));
                    if ($blockFace->hasSameTypeId(VanillaBlocks::WATER()) || $blockFace->hasSameTypeId(VanillaBlocks::LAVA())) {
                        return false;
                    }
                }
            }
        }

        if ($block->hasSameTypeId(VanillaBlocks::STONE()) || $block->hasSameTypeId(VanillaBlocks::DIRT()) || $block->hasSameTypeId(VanillaBlocks::GRASS()) || $block->hasSameTypeId(VanillaBlocks::HARDENED_CLAY()) || $block->hasSameTypeId(VanillaBlocks::STAINED_CLAY()) || $block->hasSameTypeId(VanillaBlocks::SANDSTONE()) || $block->hasSameTypeId(VanillaBlocks::RED_SANDSTONE()) || $block->hasSameTypeId(VanillaBlocks::MYCELIUM()) || $block->hasSameTypeId(VanillaBlocks::SNOW_LAYER())) {
            return true;
        }
                                                                                                                    // \/ Check for liquid
        return ($block->hasSameTypeId(VanillaBlocks::SAND()) || $block->hasSameTypeId(VanillaBlocks::GRAVEL())) && !($blockAbove->hasSameTypeId(VanillaBlocks::WATER()) || $blockAbove->hasSameTypeId(VanillaBlocks::LAVA()));
    }
}