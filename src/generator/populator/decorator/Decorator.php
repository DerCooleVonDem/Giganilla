<?php

namespace JonasWindmann\Giganilla\generator\populator\decorator;

use JonasWindmann\Giganilla\generator\populator\IPopulator;
use JonasWindmann\Giganilla\GigaRandom;
use pocketmine\world\ChunkManager;

abstract class Decorator implements IPopulator {
    protected int $amount = 0;

    public function setAmount(int $amount): void {
        $this->amount = $amount;
    }

    public function populate(ChunkManager $world, GigaRandom $random, int $chunkX, int $chunkZ): void {
        for ($i = 0; $i < $this->amount; ++$i) {
            $this->decorate($world, $random, $chunkX, $chunkZ);
        }
    }

    abstract protected function decorate(ChunkManager $world, GigaRandom $random, int $chunkX, int $chunkZ): void;
}