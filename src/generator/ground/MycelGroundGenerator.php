<?php

namespace JonasWindmann\Giganilla\generator\ground;

use pocketmine\block\VanillaBlocks;

class MycelGroundGenerator extends GroundGenerator {
    public function __construct() {
        $this->topMaterial = VanillaBlocks::MYCELIUM();
    }
}