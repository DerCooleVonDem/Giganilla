<?php

namespace JonasWindmann\Giganilla\generator\ground;

use JonasWindmann\Giganilla\GigaRandom;
use pocketmine\block\Block;
use pocketmine\block\VanillaBlocks;
use pocketmine\world\ChunkManager;

class GravelPatchGroundGenerator extends GroundGenerator {
    // Assuming topMaterial and groundMaterial are properties of GravelPatchGroundGenerator
    public Block $topMaterial;
    public Block $groundMaterial;

    public function GenerateTerrainColumn(ChunkManager $world, GigaRandom $random, int $x, int $z, int $biome, float $surfaceNoise): void
    {
        $this->topMaterial = VanillaBlocks::GRASS();

        if ($surfaceNoise < -1.0 || $surfaceNoise > 2.0) {
            $this->groundMaterial = VanillaBlocks::GRAVEL();
        } else {
            $this->groundMaterial = VanillaBlocks::DIRT();
        }

        parent::GenerateTerrainColumn($world, $random, $x, $z, $biome, $surfaceNoise);
    }
}