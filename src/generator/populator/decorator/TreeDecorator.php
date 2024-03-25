<?php

namespace JonasWindmann\Giganilla\generator\populator\decorator;

use JonasWindmann\Giganilla\generator\populator\decorator\objects\GenericTree;
use JonasWindmann\Giganilla\GigaRandom;
use pocketmine\world\BlockTransaction;
use pocketmine\world\ChunkManager;

class TreeDecorator extends Decorator {
    private $decorations = [];

    public function setTrees(array $decorations): void {
        $this->decorations = $decorations;
    }

    public function getRandomTree(GigaRandom $random): ?GenericTree {
        $totalWeight = array_sum(array_column($this->decorations, 0));

        $weight = $random->nextIntWithBound($totalWeight);
        foreach ($this->decorations as $deco) {
            $weight -= $deco[0];
            if ($weight < 0) {
                return $deco[1];
            }
        }

        return null;
    }

    public function decorate(ChunkManager $world, GigaRandom $random, int $chunkX, int $chunkZ): void {
        $chunk = $world->getChunk($chunkX, $chunkZ);

        $sourceX = ($chunkX << 4) + $random->nextIntWithBound(16);
        $sourceZ = ($chunkZ << 4) + $random->nextIntWithBound(16);
        $sourceY = $chunk->getHighestBlockAt($sourceX & 0x0f, $sourceZ & 0x0f);

        $tree = $this->getRandomTree($random);
        if ($tree !== null) {
            $txn = new BlockTransaction($world);
            $tree->initialize($random, $txn);

            if ($tree->generate($world, $random, $sourceX, $sourceY, $sourceZ)) {
                $txn->apply();
            }
        }
    }

    public function populate(ChunkManager $world, GigaRandom $random, int $chunkX, int $chunkZ): void {
        $treeAmount = $this->amount;
        if ($random->nextIntWithBound(10) === 0) {
            ++$treeAmount;
        }

        for ($i = 0; $i < $treeAmount; ++$i) {
            $this->decorate($world, $random, $chunkX, $chunkZ);
        }
    }
}