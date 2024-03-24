<?php

namespace JonasWindmann\Giganilla\generator\populator;

use JonasWindmann\Giganilla\biome\BiomeList;
use JonasWindmann\Giganilla\generator\populator\decorator\DoublePlantDecorator;
use pocketmine\block\VanillaBlocks;

class SunflowerPlainsPopulator extends PlainsPopulator {

    public function __construct() {
        parent::__construct();
        $this->doublePlantDecorator = new DoublePlantDecorator();
    }

    public function initPopulators(): void {
        parent::initPopulators();

        $this->doublePlantDecorator->setAmount(10);
        $this->doublePlantDecorator->setDoublePlants([1 => VanillaBlocks::SUNFLOWER()]);
    }

    public function getBiomes(): array {
        return [BiomeList::SUNFLOWER_PLAINS];
    }
}
