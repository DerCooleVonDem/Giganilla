<?php

namespace JonasWindmann\Giganilla\generator\populator;

use JonasWindmann\Giganilla\GigaRandom;
use pocketmine\world\ChunkManager;

interface IPopulator {
    public function Populate(ChunkManager $world, GigaRandom $random, int $chunkX, int $chunkZ);
}