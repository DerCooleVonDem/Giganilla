<?php

namespace JonasWindmann\Giganilla\generator\populator\decorator\objects;

use JonasWindmann\Giganilla\GigaRandom;
use pocketmine\world\BlockTransaction;

class JungleTree extends GenericTree {
    public function initialize(GigaRandom $random, BlockTransaction $txn): void {
        parent::initialize($random, $txn);

        $this->setHeight($random->nextIntWithBound(7) + 4);
        $this->setType(GenericTree::MAGIC_NUMBER_JUNGLE);
    }
}
