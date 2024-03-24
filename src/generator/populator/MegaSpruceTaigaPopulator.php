<?php

namespace JonasWindmann\Giganilla\generator\populator;

use JonasWindmann\Giganilla\biome\BiomeList;
use JonasWindmann\Giganilla\generator\populator\decorator\objects\MegaSpruceTree;
use JonasWindmann\Giganilla\generator\populator\decorator\objects\RedwoodTree;
use JonasWindmann\Giganilla\generator\populator\decorator\objects\TallRedwoodTree;

class MegaSpruceTaigaPopulator extends MegaTaigaPopulator {
    private RedwoodTree $redwoodTree;
    private TallRedwoodTree $tallRedwoodTree;
    private MegaSpruceTree $megaSpruceTree;

    public function __construct() {
        parent::__construct();

        $this->redwoodTree = new RedwoodTree();
        $this->tallRedwoodTree = new TallRedwoodTree();
        $this->megaSpruceTree = new MegaSpruceTree();
    }

    public function initPopulators(): void {
        parent::initPopulators();
        $this->treeDecorator->setTrees([
            [44, $this->redwoodTree],
            [22, $this->tallRedwoodTree],
            [33, $this->megaSpruceTree]
        ]);
    }

    public function getBiomes(): array {
        return [BiomeList::MEGA_SPRUCE_TAIGA, BiomeList::MEGA_SPRUCE_TAIGA_HILLS];
    }
}
