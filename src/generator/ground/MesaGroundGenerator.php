<?php

namespace JonasWindmann\Giganilla\generator\ground;

use JonasWindmann\Giganilla\GigaRandom;
use JonasWindmann\Giganilla\noise\octave\SimplexOctaveGenerator;
use pocketmine\block\utils\DirtType;
use pocketmine\block\utils\DyeColor;
use pocketmine\block\VanillaBlocks;
use pocketmine\world\ChunkManager;

class MesaGroundGenerator extends GroundGenerator
{
    private MesaType $type;
    private int $seed;
    private GigaRandom $random;

    const COLOUR_LAYER_SIZE = 64;
    private array $colourLayer;
    private SimplexOctaveGenerator $colorNoise;
    private SimplexOctaveGenerator $canyonHeightNoise;
    private SimplexOctaveGenerator $canyonScaleNoise;

    public function __construct(MesaType $type)
    {
        $this->random = new GigaRandom(0); // See regarding: https://github.com/NetherGamesMC/ext-vanillagenerator/blob/abd059fd2ca79888aab3b9c5070d83ceea55fada/lib/generator/ground/MesaGroundGenerator.h#L30
                                                 // and https://github.com/NetherGamesMC/ext-vanillagenerator/blob/abd059fd2ca79888aab3b9c5070d83ceea55fada/lib/generator/ground/MesaGroundGenerator.h#L35


        $this->type = $type;
        $this->colourLayer = array_fill(0, self::COLOUR_LAYER_SIZE, -1); // Hardened clay

        $this->topMaterial = VanillaBlocks::RED_SAND();
        $this->groundMaterial = VanillaBlocks::STAINED_CLAY()->setColor(DyeColor::ORANGE); // Orange block

        $this->colorNoise = new SimplexOctaveGenerator($this->random, 1, 0, 0, 0);
        $this->canyonHeightNoise = new SimplexOctaveGenerator($this->random, 4, 0, 0, 0);
        $this->canyonScaleNoise = new SimplexOctaveGenerator($this->random, 1, 0, 0, 0);

        $this->colorNoise->SetScale(1 / 512.0);
        $this->canyonHeightNoise->SetScale(1 / 4.0);
        $this->canyonScaleNoise->SetScale(1 / 512.0);
    }

    public function Initialize(int $seed): void
    {
        if ($seed != $this->seed) {
            $this->seed = $seed;
            $this->random = new GigaRandom($seed);

            $this->InitializeColorLayers($this->random);
        }
    }

    public function InitializeColorLayers(GigaRandom $random): void
    {
        $this->colourLayer = array_fill(0, 64, -1);

        $i = 0;
        while ($i < count($this->colourLayer)) {
            $i += $random->NextIntWithBound(5) + 1;
            if ($i < count($this->colourLayer)) {
                $this->colourLayer[$i++] = 1; // orange
            }
        }

        $this->SetRandomLayerColor($random, 2, 1, DyeColor::YELLOW); // yellow
        $this->SetRandomLayerColor($random, 2, 2, DyeColor::BROWN); // brown
        $this->SetRandomLayerColor($random, 2, 1, DyeColor::RED); // red

        $j = 0;
        for ($i = 0; $i < $random->NextIntWithBound(3) + 3; $i++) {
            $j += $random->NextIntWithBound(16) + 4;
            if ($j >= count($this->colourLayer)) {
                break;
            }

            if ($random->NextIntWithBound(2) == 0 || $j < count($this->colourLayer) - 1 && $random->NextIntWithBound(2) == 0) {
                $this->colourLayer[$j - 1] = 8; // light gray
            } else {
                $this->colourLayer[$j] = 0; // white
            }
        }
    }

    public function SetRandomLayerColor(GigaRandom $random, int $minLayerCount, int $minLayerHeight, DyeColor $color): void
    {
        for ($i = 0; $i < $random->NextIntWithBound(4) + $minLayerCount; $i++) {
            $j = $random->NextIntWithBound(64);

            $k = 0;
            while ($k < $random->NextIntWithBound(3) + $minLayerHeight && $j < 64) {
                $this->colourLayer[$j++] = $color;
                $k++;
            }
        }
    }

    public function SetColoredGroundLayer(ChunkManager $world, int $x, int $y, int $z, DyeColor $color): void
    {
        if ($color >= 0 && $color <= 15) {
            $world->SetBlockAt($x, $y, $z, VanillaBlocks::STAINED_CLAY()->setColor($color));
        } else {
            $world->SetBlockAt($x, $y, $z, VanillaBlocks::HARDENED_CLAY());
        }
    }

    public function GenerateTerrainColumn(ChunkManager $world, GigaRandom $random, int $x, int $z, int $biome, float $surfaceNoise): void
    {
        $this->Initialize($random->GetSeed());

        $seaLevel = 64;

        $topMat = null;
        $groundMat = $this->groundMaterial;

        $surfaceHeight = max(floor($surfaceNoise / 3.0 + 3.0 + $random->NextFloat() * 0.25), 1);
        $colored = cos($surfaceNoise / 3.0 * M_PI) <= 0;
        $bryceCanyonHeight = 0;
        if ($this->type == MesaType::BRYCE) {
            $noiseX = ($x & 0xFFFFFFF0) + ($z & 0xF);
            $noiseZ = ($z & 0xFFFFFFF0) + ($x & 0xF);
            $noiseCanyonHeight = max(abs($surfaceNoise), $this->canyonHeightNoise->Noise($noiseX, $noiseZ, 0, 0.5, 2.0, false));
            if ($noiseCanyonHeight > 0) {
                $heightScale = abs($this->canyonScaleNoise->Noise($noiseX, $noiseZ, 0, 0.5, 2.0, false));
                $bryceCanyonHeight = pow($noiseCanyonHeight, 2) * 2.5;
                $maxHeight = ceil(50 * $heightScale) + 14;
                if ($bryceCanyonHeight > $maxHeight) {
                    $bryceCanyonHeight = $maxHeight;
                }

                $bryceCanyonHeight += $seaLevel;
            }
        }

        $chunkX = $x;
        $chunkZ = $z;

        $deep = -1;
        $groundSet = false;

        $grass = VanillaBlocks::GRASS();
        $coarseDirt = VanillaBlocks::DIRT()->setDirtType(DirtType::COARSE);

        $airBlock = VanillaBlocks::AIR();
        $stoneBlock = VanillaBlocks::STONE();
        $bedrockBlock = VanillaBlocks::BEDROCK();
        $waterBlock = VanillaBlocks::WATER();
        $stillWaterBlock = VanillaBlocks::WATER()->getStillForm();

        for ($y = 255; $y >= 0; --$y) {
            $chunk = $world->getChunk($x >> 4, $z >> 4);
            $highest_block_at = $chunk->getHighestBlockAt($x, $z);
            if ($y < (int) $bryceCanyonHeight && $world->getBlockAt($x, $y, $z)->hasSameTypeId($airBlock) && !($highest_block_at == $waterBlock || $highest_block_at == $stillWaterBlock)) {
                $world->setBlockAt($x, $y, $z, $stoneBlock);
            }
            if ($y <= $random->NextIntWithBound(5)) {
                $world->setBlockAt($x, $y, $z, $bedrockBlock);
            } else {
                $matId = $world->getBlockAt($x, $y, $z);
                if ($matId->isSameState($airBlock)) {
                    $deep = -1;
                } elseif ($matId->isSameState($stoneBlock)) {
                    if ($deep == -1) {
                        $groundSet = false;
                        if ($y >= $seaLevel - 5 && $y <= $seaLevel) {
                            $groundMat = $this->groundMaterial;
                        }

                        $deep = $surfaceHeight + max(0, $y - $seaLevel - 1);
                        if ($y >= $seaLevel - 2) {
                            if ($this->type == MesaType::FOREST_TYPE && $y > $seaLevel + 22 + ($surfaceHeight << 1)) {
                                $topMat = $colored ? $grass : $coarseDirt;
                                $world->SetBlockAt($x, $y, $z, $topMat);
                            } elseif ($y > $seaLevel + 2 + $surfaceHeight) {
                                $color = $this->colourLayer[(($y + (int) round($this->colorNoise->Noise($chunkX, $chunkZ, 0, 0.5, 2.0, false) * 2.0)) % 64)];
                                $this->SetColoredGroundLayer($world, $x, $y, $z, $y < $seaLevel || $y > 128 ? 1 : ($colored ? $color : -1));
                            } else {
                                $world->SetBlockAt($x, $y, $z, $this->topMaterial);
                                $groundSet = true;
                            }
                        } else {
                            $world->SetBlockAt($x, $y, $z, $groundMat);
                        }
                    } elseif ($deep > 0) {
                        --$deep;
                        if ($groundSet) {
                            $world->SetBlockAt($x, $y, $z, $this->groundMaterial);
                        } else {
                            $color = $this->colourLayer[(($y + (int) round($this->colorNoise->Noise($chunkX, $chunkZ, 0, 0.5, 2.0, false) * 2.0)) % 64)];
                            $this->SetColoredGroundLayer($world, $x, $y, $z, $color);
                        }
                    }
                }
            }
        }
    }
}