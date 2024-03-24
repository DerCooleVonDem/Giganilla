<?php

namespace JonasWindmann\Giganilla\generator\populator\decorator\objects;

class TallBirchTree extends BirchTree {
    public function initialize($random, $txn): void
    {
        parent::initialize($random, $txn);

        $this->setHeight($this->height + $random->nextIntWithBound(7));
    }
}
