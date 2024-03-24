<?php

namespace JonasWindmann\Giganilla\generator\populator\decorator\objects;

use JonasWindmann\Giganilla\GigaRandom;
use pocketmine\math\Vector3;
use pocketmine\world\BlockTransaction;
use pocketmine\world\ChunkManager;

class BigOakTree extends GenericTree {
    private int $maxLeafDistance = 5;
    private int $trunkHeight = 0;
    private const LEAF_DENSITY = 1.0;

    public function initialize(GigaRandom $random, BlockTransaction $txn): void {
        parent::initialize($random, $txn);
        $this->setHeight($random->nextInt(12) + 5);
    }

    public function setMaxLeafDistance(int $distance): void {
        $this->maxLeafDistance = $distance;
    }

    public function canPlace(int $baseX, int $baseY, int $baseZ, ChunkManager $world): bool {
        $from = new Vector3($baseX, $baseY, $baseZ);
        $to = new Vector3($baseX, $baseY + $this->height - 1, $baseZ);
        $blocks = $this->countAvailableBlocks($from, $to, $world);
        if ($blocks === -1) {
            return true;
        } elseif ($blocks > 5) {
            $this->height = $blocks;
            return true;
        }
        return false;
    }

    public function generate(ChunkManager $world, GigaRandom $random, int $sourceX, int $sourceY, int $sourceZ): bool {
        if (!$this->canPlaceOn($world->getBlockAt($sourceX, $sourceY - 1, $sourceZ)) || !$this->canPlace($sourceX, $sourceY, $sourceZ, $world)) {
            return false;
        }

        $this->trunkHeight = (int) ($this->height * 0.618);
        if ($this->trunkHeight >= $this->height) {
            $this->trunkHeight = $this->height - 1;
        }

        $leafNodes = $this->generateLeafNodes($sourceX, $sourceY, $sourceZ, $world, $random);

        foreach ($leafNodes as $node) {
            for ($y = 0; $y < $this->maxLeafDistance; $y++) {
                $size = $y > 0 && $y < $this->maxLeafDistance - 1.0 ? 3.0 : 2.0;
                $nodeDistance = (int) (0.618 + $size);
                for ($x = -$nodeDistance; $x <= $nodeDistance; $x++) {
                    for ($z = -$nodeDistance; $z <= $nodeDistance; $z++) {
                        $sizeX = abs($x) + 0.5;
                        $sizeZ = abs($z) + 0.5;
                        if ($sizeX * $sizeX + $sizeZ * $sizeZ <= $size * $size && in_array($world->getBlockAt($node->x, $node->y, $node->z)->getTypeId(), $this->overrides)) {
                            $this->transaction->addBlockAt($node->x + $x, $node->y + $y, $node->z + $z, $this->leavesTypes);
                        }
                    }
                }
            }
        }

        // Generate the trunk
        for ($y = 0; $y < $this->trunkHeight; $y++) {
            $this->transaction->addBlockAt($sourceX, $sourceY + $y, $sourceZ, $this->logType);
        }

        // Generate the branches
        foreach ($leafNodes as $node) {
            if (($node->branchY - $sourceY) >= ($this->height * 0.2)) {
                $base = new Vector3($sourceX, $node->branchY, $sourceZ);
                $leafNode = new Vector3($node->x, $node->y, $node->z);
                $branch = $leafNode->subtractVector($base);

                $maxDistance = max(abs($branch->getFloorY()), abs($branch->getFloorX()), abs($branch->getFloorZ()));
                if ($maxDistance > 0) {
                    $dx = $branch->x / $maxDistance;
                    $dy = $branch->y / $maxDistance;
                    $dz = $branch->z / $maxDistance;
                    for ($i = 0; $i <= $maxDistance; $i++) {
                        $newBranch = $base->add(0.5 + $i * $dx, 0.5 + $i * $dy, 0.5 + $i * $dz);
                        $x = abs($newBranch->getFloorX() - $base->getFloorX());
                        $z = abs($newBranch->getFloorZ() - $base->getFloorZ());
                        $max = max($x, $z);

                        // X axis (east/west)   = 0
                        // Z axis (north/south) = 2
                        // Y axis = 4
                        $direction = $max > 0 ? ($max == $x ? 0 : 2) : 4; // EAST / SOUTH

                        $log = clone $this->logType;
                        $this->transaction->addBlockAt($newBranch->getFloorX(), $newBranch->getFloorY(), $newBranch->getFloorZ(), $log->setAxis($direction));
                    }
                }
            }
        }

        return true;
    }

    private function countAvailableBlocks(Vector3 $from, Vector3 $to, ChunkManager $world): int {
        $n = 0;
        $target = $to->subtractVector($from);
        $maxDistance = max(abs($target->getFloorY()), abs($target->getFloorX()), abs($target->getFloorZ()));
        $dx = $target->x / $maxDistance;
        $dy = $target->y / $maxDistance;
        $dz = $target->z / $maxDistance;
        for ($i = 0; $i <= $maxDistance; $i++, $n++) {
            $target = $from->add((0.5 + $i * $dx), 0.5 + $i * $dy, 0.5 + $i * $dz);
            if ($target->getFloorY() < 0 || $target->getFloorY() > 255
                || !in_array($world->getBlockAt($target->getFloorX(), $target->getFloorY(), $target->getFloorZ())->getTypeId(), $this->overrides)) {
                return $n;
            }
        }
        return -1;
    }

    private function generateLeafNodes(int $blockX, int $blockY, int $blockZ, ChunkManager $world, GigaRandom $random): array {
        $leafNodes = [];
        $y = $blockY + $this->height - $this->maxLeafDistance;
        $trunkTopY = $blockY + $this->trunkHeight;
        $leafNodes[] = new LeafNode($blockX, $y, $blockZ, $trunkTopY);

        $nodeCount = (int) (1.382 + pow(self::LEAF_DENSITY * ($this->height / 13.0), 2.0));
        $nodeCount = $nodeCount < 1 ? 1 : $nodeCount;

        for ($l = --$y - $blockY; $l >= 0; $l--, $y--) {
            $h = $this->height / 2.0;
            $v = $h - $l;
            $f = $l < $this->height * 0.3 ? -1.0 : ($v == $h ? $h * 0.5 : ($h <= abs($v) ? 0.0 : sqrt($h * $h - $v * $v) * 0.5));
            if ($f >= 0.0) {
                for ($i = 0; $i < $nodeCount; $i++) {
                    $d1 = $f * ($random->nextFloat() + 0.328);
                    $d2 = $random->nextFloat() * M_PI * 2.0;
                    $x = round($d1 * sin($d2) + $blockX + 0.5);
                    $z = round($d1 * cos($d2) + $blockZ + 0.5);
                    if ($this->countAvailableBlocks(new Vector3($x, $y, $z), new Vector3($x, $y + $this->maxLeafDistance, $z), $world) === -1) {
                        $offX = $blockX - $x;
                        $offZ = $blockZ - $z;
                        $distance = 0.381 * hypot($offX, $offZ);
                        $branchBaseY = min($trunkTopY, (int) ($y - $distance));
                        if ($this->countAvailableBlocks(new Vector3($x, $branchBaseY, $z), new Vector3($x, $y, $z), $world) === -1) {
                            $leafNodes[] = new LeafNode($x, $y, $z, $branchBaseY);
                        }
                    }
                }
            }
        }

        return $leafNodes;
    }
}
