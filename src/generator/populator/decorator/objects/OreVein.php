<?php

namespace JonasWindmann\Giganilla\generator\populator\decorator\objects;

use JonasWindmann\Giganilla\generator\populator\decorator\OreType;
use JonasWindmann\Giganilla\GigaRandom;
use pocketmine\world\ChunkManager;

class OreVein {
    private OreType $oreType;

    public function __construct(OreType $oreType) {
        $this->oreType = $oreType;
    }

    public function generate(ChunkManager $world, GigaRandom $random, int $sourceX, int $sourceY, int $sourceZ): bool {
        $amount = $this->oreType->amount;
        $angle = $random->nextFloat() * M_PI;
        $dx1 = $sourceX + sin($angle) * $amount / 8.0;
        $dx2 = $sourceX - sin($angle) * $amount / 8.0;
        $dz1 = $sourceZ + cos($angle) * $amount / 8.0;
        $dz2 = $sourceZ - cos($angle) * $amount / 8.0;
        $dy1 = $sourceY + $random->nextIntWithBound(3) - 2;
        $dy2 = $sourceY + $random->nextIntWithBound(3) - 2;

        $succeeded = false;

        for ($i = 0; $i < $amount; $i++) {
            $originX = $dx1 + ($dx2 - $dx1) * $i / $amount;
            $originY = $dy1 + ($dy2 - $dy1) * $i / $amount;
            $originZ = $dz1 + ($dz2 - $dz1) * $i / $amount;
            $q = $random->nextFloat() * $amount / 16.0;
            $radiusH = (sin($i * M_PI / $amount) + 1 * $q + 1) / 2.0;
            $radiusV = (sin($i * M_PI / $amount) + 1 * $q + 1) / 2.0;
            for ($x = (int) ($originX - $radiusH); $x <= (int) ($originX + $radiusH); $x++) {
                $squaredNormalizedX = $this->normalizedSquaredCoordinate($originX, $radiusH, $x);

                if ($squaredNormalizedX >= 1) continue;

                for ($y = (int) ($originY - $radiusV); $y <= (int) ($originY + $radiusV); $y++) {
                    $squaredNormalizedY = $this->normalizedSquaredCoordinate($originY, $radiusV, $y);

                    if ($squaredNormalizedX + $squaredNormalizedY >= 1) continue;

                    for ($z = (int) ($originZ - $radiusH); $z <= (int) ($originZ + $radiusH); $z++) {
                        $squaredNormalizedZ = $this->normalizedSquaredCoordinate($originZ, $radiusH, $z);
                        $normalized = $squaredNormalizedX + $squaredNormalizedY + $squaredNormalizedZ;

                        if ($normalized < 1 && $world->getBlockAt($x, $y, $z)->hasSameTypeId($this->oreType->targetType)) {
                            $world->setBlockAt($x, $y, $z, $this->oreType->blockType);
                            $succeeded = true;
                        }
                    }
                }
            }
        }

        return $succeeded;
    }

    protected static function normalizedSquaredCoordinate(float $origin, float $radius, int $x): float {
        $squared_normalized_x = ($x + 0.5 - $origin) / $radius;
        $squared_normalized_x *= $squared_normalized_x;

        return $squared_normalized_x;
    }
}