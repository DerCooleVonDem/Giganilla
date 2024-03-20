<?php

namespace JonasWindmann\Giganilla\generator\ground;

use pocketmine\block\VanillaBlocks;

class SnowyGroundGenerator extends GroundGenerator {
    public function __construct() {
        $this->topMaterial = VanillaBlocks::SNOW_LAYER(); // TODO: Im unsure if its snow or snow layer | Resource https://github.com/NetherGamesMC/ext-vanillagenerator/blob/abd059fd2ca79888aab3b9c5070d83ceea55fada/lib/generator/ground/SnowyGroundGenerator.h
    }
}