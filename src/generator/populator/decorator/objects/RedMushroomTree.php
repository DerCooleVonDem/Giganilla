<?php

namespace JonasWindmann\Giganilla\generator\populator\decorator\objects;

use pocketmine\block\VanillaBlocks;

class RedMushroomTree extends BrownMushroomTree {
    public function __construct() {
        parent::__construct();
        $this->type = VanillaBlocks::RED_MUSHROOM_BLOCK();
    }
}
