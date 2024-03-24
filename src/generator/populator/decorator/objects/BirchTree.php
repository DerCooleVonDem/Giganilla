<?php

namespace JonasWindmann\Giganilla\generator\populator\decorator\objects;

class BirchTree extends GenericTree {
    public function initialize($random, $txn): void
    {
        parent::initialize($random, $txn);

        $this->setHeight($random->nextIntWithBound(3) + 5);
        $this->setType(GenericTree::MAGIC_NUMBER_BIRCH);
    }
}
