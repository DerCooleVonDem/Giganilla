<?php

namespace JonasWindmann\Giganilla\generator\ground;

use JonasWindmann\Giganilla\GigaRandom;
use pocketmine\block\Block;
use pocketmine\block\VanillaBlocks;
use pocketmine\world\ChunkManager;

class DirtPatchGroundGenerator extends GroundGenerator {
    // Assuming topMaterial and groundMaterial are properties of DirtPatchGroundGenerator
    public Block $topMaterial;
    public Block $groundMaterial;

    public function GenerateTerrainColumn(ChunkManager $world, GigaRandom $random, int $x, int $z, int $biome, float $surfaceNoise): void
    {
        if ($surfaceNoise > 1.75) {
            $this->topMaterial = VanillaBlocks::DIRT();
        } elseif ($surfaceNoise > -0.5) {
            $this->topMaterial = VanillaBlocks::PODZOL();
        } else {
            $this->topMaterial = VanillaBlocks::GRASS();
        }
        $this->groundMaterial = VanillaBlocks::DIRT();

        parent::GenerateTerrainColumn($world, $random, $x, $z, $biome, $surfaceNoise);
    }
}
