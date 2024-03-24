<?php

namespace JonasWindmann\Giganilla\generator\populator\decorator\objects;

use JonasWindmann\Giganilla\GigaRandom;
use pocketmine\world\BlockTransaction;

class MegaSpruceTree extends MegaPineTree {
    // TODO: Not sure if that is enough to generate a vanilla-like MegaSpruceTree????
    public function initialize(GigaRandom $random, BlockTransaction $txn): void {
        parent::initialize($random, $txn);
        $this->setLeavesHeight($this->leavesHeight + 10);
    }
}
