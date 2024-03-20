<?php

namespace JonasWindmann\Giganilla\generator\populator\decorator\objects;

class LeafNode {
    public int $x = 0;
    public int $y = 0;
    public int $z = 0;
    public int $branchY = 0;

    public function __construct(int $x, int $y, int $z, int $branchY) {
        $this->x = $x;
        $this->y = $y;
        $this->z = $z;
        $this->branchY = $branchY;
    }
}