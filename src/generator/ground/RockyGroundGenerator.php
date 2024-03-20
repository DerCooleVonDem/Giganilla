<?php

namespace JonasWindmann\Giganilla\generator\ground;

use pocketmine\block\VanillaBlocks;

class RockyGroundGenerator extends GroundGenerator {
    public function __construct() {
        $this->topMaterial = VanillaBlocks::STONE();
        $this->groundMaterial = VanillaBlocks::STONE();
    }
}