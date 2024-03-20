<?php

namespace JonasWindmann\Giganilla\generator\ground;

use pocketmine\block\VanillaBlocks;

class SandyGroundGenerator extends GroundGenerator {
    public function __construct() {
        $this->topMaterial = VanillaBlocks::SAND();
        $this->groundMaterial = VanillaBlocks::SAND();
    }
}