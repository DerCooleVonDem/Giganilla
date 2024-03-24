<?php

namespace JonasWindmann\Giganilla\generator;

use JonasWindmann\Giganilla\biome\BiomeClimate;
use JonasWindmann\Giganilla\biome\BiomeHeightManager;
use JonasWindmann\Giganilla\biome\BiomeList;
use JonasWindmann\Giganilla\generator\biomegrid\MapLayerPair;
use JonasWindmann\Giganilla\generator\biomegrid\VanillaBiomeGrid;
use JonasWindmann\Giganilla\generator\carver\CaveCarver;
use JonasWindmann\Giganilla\generator\ground\DirtAndStonePatchGroundGenerator;
use JonasWindmann\Giganilla\generator\ground\DirtPatchGroundGenerator;
use JonasWindmann\Giganilla\generator\ground\GravelPatchGroundGenerator;
use JonasWindmann\Giganilla\generator\ground\GroundGenerator;
use JonasWindmann\Giganilla\generator\ground\MesaGroundGenerator;
use JonasWindmann\Giganilla\generator\ground\MesaType;
use JonasWindmann\Giganilla\generator\ground\MycelGroundGenerator;
use JonasWindmann\Giganilla\generator\ground\RockyGroundGenerator;
use JonasWindmann\Giganilla\generator\ground\SandyGroundGenerator;
use JonasWindmann\Giganilla\generator\ground\SnowyGroundGenerator;
use JonasWindmann\Giganilla\generator\ground\StonePatchGroundGenerator;
use JonasWindmann\Giganilla\generator\octave\WorldOctaves;
use JonasWindmann\Giganilla\generator\populator\OverworldPopulator;
use JonasWindmann\Giganilla\GigaRandom;
use JonasWindmann\Giganilla\noise\octave\PerlinOctaveGenerator;
use JonasWindmann\Giganilla\noise\octave\SimplexOctaveGenerator;
use pocketmine\block\VanillaBlocks;
use pocketmine\world\ChunkManager;
use pocketmine\world\generator\Generator;
use pocketmine\world\World;

class Giganilla extends Generator
{
    const COORDINATE_SCALE = 684.412;
    const HEIGHT_SCALE = 684.412;
    const HEIGHT_NOISE_SCALE_X = 200.0;
    const HEIGHT_NOISE_SCALE_Z = 200.0;
    const DETAIL_NOISE_SCALE_X = 80.0;
    const DETAIL_NOISE_SCALE_Y = 160.0;
    const DETAIL_NOISE_SCALE_Z = 80.0;
    const SURFACE_SCALE = 0.0625;
    const BASE_SIZE = 8.5;
    const STRETCH_Y = 12.0;
    const BIOME_HEIGHT_OFFSET = 0.0;
    const BIOME_HEIGHT_WEIGHT = 1.0;
    const BIOME_SCALE_OFFSET = 0.0;
    const BIOME_SCALE_WEIGHT = 1.0;
    const DENSITY_FILL_MODE = 0;
    const DENSITY_FILL_SEA_MODE = 0;
    const DENSITY_FILL_OFFSET = 0.0;

    const IS_UHC = false; // TODO: Remove later bc this is just debug!

    private bool $isUHC;
    private MapLayerPair $mapLayer;
    private GigaRandom $ownRandom; // $random
    private WorldOctaves $octaves;
    private GroundGenerator $defaultGenerator;
    private OverworldPopulator $populators;
    private array $elevationWeight;
    private array $groundMap;
    private SandyGroundGenerator $sandyGroundGenerator;
    private RockyGroundGenerator $rockyGroundGenerator;
    private SnowyGroundGenerator $snowyGroundGenerator;
    private MycelGroundGenerator $mycelGroundGenerator;
    private StonePatchGroundGenerator $stonePatchGroundGenerator;
    private GravelPatchGroundGenerator $gravelPatchGroundGenerator;
    private DirtAndStonePatchGroundGenerator $dirtAndStonePatchGroundGenerator;
    private DirtPatchGroundGenerator $dirtPatchGroundGenerator;
    private MesaGroundGenerator $defaultMesaGroundGenerator;
    private MesaGroundGenerator $bryceMesaGroundGenerator;
    private MesaGroundGenerator $forestMesaGroundGenerator;
    private CaveCarver $caveGenerator;

    public function __construct(int $seed, string $preset)
    {
        parent::__construct($seed, $preset);

        // Ground generators
        $this->defaultGenerator = new GroundGenerator();
        $this->sandyGroundGenerator = new SandyGroundGenerator();
        $this->rockyGroundGenerator = new RockyGroundGenerator();
        $this->snowyGroundGenerator = new SnowyGroundGenerator();
        $this->mycelGroundGenerator = new MycelGroundGenerator();
        $this->stonePatchGroundGenerator = new StonePatchGroundGenerator();
        $this->gravelPatchGroundGenerator = new GravelPatchGroundGenerator();
        $this->dirtAndStonePatchGroundGenerator = new DirtAndStonePatchGroundGenerator();
        $this->dirtPatchGroundGenerator = new DirtPatchGroundGenerator();
        $this->defaultMesaGroundGenerator = new MesaGroundGenerator(MesaType::NORMAl);
        $this->bryceMesaGroundGenerator = new MesaGroundGenerator(MesaType::BRYCE);
        $this->forestMesaGroundGenerator = new MesaGroundGenerator(MesaType::FOREST_TYPE);

        // Caves
        $this->caveGenerator = new CaveCarver();

        $this->populators = new OverworldPopulator();

        $this->isUHC = self::IS_UHC;
        // Initialize other properties here
        // For example:
        $this->ownRandom = new GigaRandom($seed);
        $this->octaves = new WorldOctaves();

        $octaveRandom = new GigaRandom($seed);
        $this->octaves->height = new PerlinOctaveGenerator($octaveRandom, 16, 5, 1, 5);
        $this->octaves->roughness = new PerlinOctaveGenerator($octaveRandom, 16, 5, 33, 5);
        $this->octaves->roughness2 = new PerlinOctaveGenerator($octaveRandom, 16, 5, 33, 5);
        $this->octaves->detail = new PerlinOctaveGenerator($octaveRandom, 8, 5, 33, 5);
        $this->octaves->surface = new SimplexOctaveGenerator($octaveRandom, 4, 16, 1, 16);

        $biomeClimate = new BiomeClimate();
        $biomeClimate->Init($this->isUHC);

        $this->octaves->height->SetXScale(self::HEIGHT_NOISE_SCALE_X);
        $this->octaves->height->SetYScale(self::HEIGHT_NOISE_SCALE_Z);

        $this->octaves->roughness->SetXScale(self::COORDINATE_SCALE);
        $this->octaves->roughness->SetYScale(self::HEIGHT_SCALE);
        $this->octaves->roughness->SetZScale(self::COORDINATE_SCALE);

        $this->octaves->roughness2->SetXScale(self::COORDINATE_SCALE);
        $this->octaves->roughness2->SetYScale(self::HEIGHT_SCALE);
        $this->octaves->roughness2->SetZScale(self::COORDINATE_SCALE);

        $this->octaves->detail->SetXScale(self::COORDINATE_SCALE / self::DETAIL_NOISE_SCALE_X);
        $this->octaves->detail->SetYScale(self::HEIGHT_SCALE / self::DETAIL_NOISE_SCALE_Y);
        $this->octaves->detail->SetZScale(self::COORDINATE_SCALE / self::DETAIL_NOISE_SCALE_Z);

        $this->octaves->surface->SetScale(self::SURFACE_SCALE);

        $this->insertGroundmap([BiomeList::BEACH, BiomeList::COLD_BEACH, BiomeList::DESERT, BiomeList::DESERT_HILLS, BiomeList::DESERT_MOUNTAINS], $this->sandyGroundGenerator);
        $this->insertGroundmap([BiomeList::STONE_BEACH], $this->rockyGroundGenerator);
        $this->insertGroundmap([BiomeList::ICE_PLAINS_SPIKES], $this->snowyGroundGenerator);
        $this->insertGroundmap([BiomeList::MUSHROOM_ISLAND, BiomeList::MUSHROOM_SHORE], $this->mycelGroundGenerator);
        $this->insertGroundmap([BiomeList::EXTREME_HILLS], $this->stonePatchGroundGenerator);
        // EXTREME_HILLS_MOUNTAINS, EXTREME_HILLS_PLUS_MOUNTAINS
        $this->insertGroundmap([BiomeList::EXTREME_HILLS_MOUNTAINS, BiomeList::EXTREME_HILLS_PLUS_MOUNTAINS], $this->gravelPatchGroundGenerator);
        // SAVANNA_MOUNTAINS, SAVANNA_PLATEAU_MOUNTAINS
        $this->insertGroundmap([BiomeList::SAVANNA_MOUNTAINS, BiomeList::SAVANNA_PLATEAU_MOUNTAINS], $this->dirtAndStonePatchGroundGenerator);

        if (!$this->isUHC) {
            // MEGA_TAIGA, MEGA_TAIGA_HILLS, MEGA_SPRUCE_TAIGA, MEGA_SPRUCE_TAIGA_HILLS}, dirtPatchGroundGenerator_
            $this->insertGroundmap([BiomeList::MEGA_TAIGA, BiomeList::MEGA_TAIGA_HILLS, BiomeList::MEGA_SPRUCE_TAIGA, BiomeList::MEGA_SPRUCE_TAIGA_HILLS], $this->dirtPatchGroundGenerator);
        }

        //MESA, MESA_PLATEAU, MESA_PLATEAU_FOREST}, defaultMesaGroundGenerator_
        $this->insertGroundmap([BiomeList::MESA, BiomeList::MESA_PLATEAU, BiomeList::MESA_PLATEAU_FOREST], $this->defaultMesaGroundGenerator);
        // {MESA_BRYCE}, bryceMesaGroundGenerator_
        $this->insertGroundmap([BiomeList::MESA_BRYCE], $this->bryceMesaGroundGenerator);
        // MESA_PLATEAU_FOREST, MESA_PLATEAU_FOREST_MOUNTAINS}, forestMesaGroundGenerator_
        $this->insertGroundmap([BiomeList::MESA_PLATEAU_FOREST, BiomeList::MESA_PLATEAU_FOREST_MOUNTAINS], $this->forestMesaGroundGenerator);

        for ($x = 0; $x < 5; ++$x) {
            for ($z = 0; $z < 5; ++$z) {
                $sqX = $x - 2;
                $sqX *= $sqX;
                $sqZ = $z - 2;
                $sqZ *= $sqZ;

                $value = 10.0 / sqrt($sqX + $sqZ + 0.2);

                // Assuming ElevationWeightHash is a function that takes two arguments and returns a hash
                $this->elevationWeight[$this->ElevationWeightHash($x, $z)] = $value;
            }
        }
    }

    public function insertGroundmap(array $biomeIds, GroundGenerator $generator): void
    {
        foreach ($biomeIds as $biomeId) {
            $this->groundMap[$biomeId] = $generator;
        }
    }

    public function generateChunk(ChunkManager $world, int $chunkX, int $chunkZ): void
    {
        $read = $this->mapLayer->highResolution->generateValues($chunkX * 16, $chunkZ * 16, 16, 16);

        $this->generateChunkData($world, $chunkX, $chunkZ, new VanillaBiomeGrid($read));

        $this->caveGenerator->generate($world, $this->ownRandom, $chunkX, $chunkZ, $world->getChunk($chunkX, $chunkZ));
    }

    public function populateChunk(ChunkManager $world, int $chunkX, int $chunkZ): void
    {
        $this->populators->Populate($world, $this->ownRandom, $chunkX, $chunkZ);
    }

    private function GenerateChunkData(ChunkManager $world, int $chunkX, int $chunkZ, VanillaBiomeGrid $biome): void
    {
        $this->generateRawTerrain($world, $chunkX, $chunkZ);

        $cx = $chunkX << 4;
        $cz = $chunkZ << 4;

        $octaveGenerator = $this->octaves->surface;
        $sizeX = $octaveGenerator->getSizeX();
        $sizeZ = $octaveGenerator->getSizeZ();

        $surfaceNoise = $octaveGenerator->getFractalBrownianMotion($cx, 0.0, $cz, 0.5, 0.5);

        $chunk = $world->getChunk($chunkX, $chunkZ);

        for ($x = 0; $x < $sizeX; ++$x) {
            for ($z = 0; $z < $sizeZ; ++$z) {
                $id = $biome->getBiome($x, $z);

                if ($this->isUHC && ($id == 0 || $id == 6 || $id == 10 || ($id >= 21 && $id <= 24) || ($id >= 32 && $id <= 33) || $id == 134
                        || $id == 149 || $id == 151 || $id == 160 || $id == 161)) {
                    $id = 132;
                }

                for($y = World::Y_MIN; $y < World::Y_MAX; $y++){
                    $chunk->setBiomeId($x, $y, $z, $id);
                }

                $found = false;
                foreach ($this->groundMap as $mappings) {
                    $biomes = $mappings['first'];
                    if (in_array($id, $biomes)) {
                        $mappings['second']->generateTerrainColumn($world, $this->ownRandom, $cx + $x, $cz + $z, $id, $surfaceNoise[$x | $z << 4]);

                        $found = true;
                        break;
                    }
                }

                if (!$found) {
                    $this->defaultGenerator->generateTerrainColumn($world, $this->ownRandom, $cx + $x, $cz + $z, $id, $surfaceNoise[$x | $z << 4]);
                }
            }
        }
    }

    private function GenerateRawTerrain(ChunkManager $world, int $chunkX, int $chunkZ): void{
        $density = $this->generateTerrainDensity($chunkX, $chunkZ);

        $seaLevel = 64;

        $fill = abs(self::DENSITY_FILL_MODE);
        $seaFill = self::DENSITY_FILL_SEA_MODE;
        $densityOffset = self::DENSITY_FILL_OFFSET;

        $stillWater = VanillaBlocks::WATER()->getStillForm();
        $water = VanillaBlocks::WATER();
        $stone = VanillaBlocks::STONE();

        $chunk = $world->getChunk($chunkX, $chunkZ);
        for ($i = 0; $i < 5 - 1; ++$i) {
            for ($j = 0; $j < 5 - 1; ++$j) {
                for ($k = 0; $k < 33 - 1; ++$k) {
                    $d1 = $density[$this->densityHash($i, $j, $k)];
                    $d2 = $density[$this->densityHash($i + 1, $j, $k)];
                    $d3 = $density[$this->densityHash($i, $j + 1, $k)];
                    $d4 = $density[$this->densityHash($i + 1, $j + 1, $k)];
                    $d5 = ($density[$this->densityHash($i, $j, $k + 1)] - $d1) / 8;
                    $d6 = ($density[$this->densityHash($i + 1, $j, $k + 1)] - $d2) / 8;
                    $d7 = ($density[$this->densityHash($i, $j + 1, $k + 1)] - $d3) / 8;
                    $d8 = ($density[$this->densityHash($i + 1, $j + 1, $k + 1)] - $d4) / 8;

                    for ($l = 0; $l < 8; ++$l) {
                        $d9 = $d1;
                        $d10 = $d3;

                        $yPos = $l + ($k << 3);
                        $yBlockPos = $yPos & 0xf;
                        $subChunk = $chunk->getSubChunk($yPos >> 4);
                        for ($m = 0; $m < 4; ++$m) {
                            $dens = $d9;
                            for ($n = 0; $n < 4; ++$n) {
                                if ($fill == 1 || $fill == 10 || $fill == 13 || $fill == 16) {
                                    $subChunk->setBlockStateId($m + ($i << 2), $yBlockPos, $n + ($j << 2), $water->getStateId());
                                } elseif ($fill == 2 || $fill == 9 || $fill == 12 || $fill == 15) {
                                    $subChunk->setBlockStateId($m + ($i << 2), $yBlockPos, $n + ($j << 2), $stone->getStateId());
                                }

                                if (($dens > $densityOffset && $fill > -1) || ($dens <= $densityOffset && $fill < 0)) {
                                    if ($fill == 0 || $fill == 3 || $fill == 6 || $fill == 9 || $fill == 12) {
                                        $subChunk->setBlockStateId($m + ($i << 2), $yBlockPos, $n + ($j << 2), $stone->getStateId());
                                    } elseif ($fill == 2 || $fill == 7 || $fill == 10 || $fill == 16) {
                                        $subChunk->setBlockStateId($m + ($i << 2), $yBlockPos, $n + ($j << 2), $stillWater->getStateId());
                                    }
                                } elseif (($yPos < $seaLevel - 1 && $seaFill == 0) || ($yPos >= $seaLevel - 1 && $seaFill == 1)) {
                                    if ($fill == 0 || $fill == 3 || $fill == 7 || $fill == 10 || $fill == 13) {
                                        $subChunk->setBlockStateId($m + ($i << 2), $yBlockPos, $n + ($j << 2), $stillWater->getStateId());
                                    } elseif ($fill == 1 || $fill == 6 || $fill == 9 || $fill == 15) {
                                        $subChunk->setBlockStateId($m + ($i << 2), $yBlockPos, $n + ($j << 2), $stone->getStateId());
                                    }
                                }

                                $dens += ($d10 - $d9) / 4;
                            }

                            $d9 += ($d2 - $d1) / 4;
                            $d10 += ($d4 - $d3) / 4;
                        }

                        $d1 += $d5;
                        $d3 += $d7;
                        $d2 += $d6;
                        $d4 += $d8;
                    }
                }
            }
        }
    }

    private function GenerateTerrainDensity(int $x, int $z): array
    {
        $density = []; // TODO: TerrainDensity type

        // Scaling chunk x and z coordinates (4x)
        $x <<= 2;
        $z <<= 2;

        // Assuming mapLayer_.lowResolution->GenerateValues returns a 2D array
        $biomeGrid = $this->mapLayer->lowResolution->generateValues($x, $z, 10, 10);

        // Assuming octaves_ is an object with height, roughness, roughness2, and detail properties
        // Each property is an object with a GetFractalBrownianMotion method
        $heightNoise = $this->octaves->height->getFractalBrownianMotion($x, 0, $z, 0.5, 2.0);
        $roughnessNoise = $this->octaves->roughness->getFractalBrownianMotion($x, 0, $z, 0.5, 2.0);
        $roughnessNoise2 = $this->octaves->roughness2->getFractalBrownianMotion($x, 0, $z, 0.5, 2.0);
        $detailNoise = $this->octaves->detail->getFractalBrownianMotion($x, 0, $z, 0.5, 2.0);

        $index = 0;
        $indexHeight = 0;

        for ($i = 0; $i < 5; ++$i) {
            for ($j = 0; $j < 5; ++$j) {
                $avgHeightScale = 0.0;
                $avgHeightBase = 0.0;
                $totalWeight = 0.0;

                $biome = $biomeGrid[$i + 2 + ($j + 2) * 10];
                $biomeHeight = BiomeHeightManager::get($biome);

                for ($m = 0; $m < 5; ++$m) {
                    for ($n = 0; $n < 5; ++$n) {
                        $nearBiome = $biomeGrid[$i + $m + ($j + $n) * 10];
                        $nearBiomeHeight = BiomeHeightManager::get($nearBiome);

                        $heightBase = self::BIOME_HEIGHT_OFFSET + $nearBiomeHeight->height * self::BIOME_HEIGHT_WEIGHT;
                        $heightScale = self::BIOME_SCALE_OFFSET + $nearBiomeHeight->scale * self::BIOME_SCALE_WEIGHT;

                        $weight = $this->elevationWeight[$this->elevationWeightHash($m, $n)] / ($heightBase + 2.0);
                        if ($nearBiomeHeight->height > $biomeHeight->height) {
                            $weight *= 0.5;
                        }

                        $avgHeightScale += $heightScale * $weight;
                        $avgHeightBase += $heightBase * $weight;
                        $totalWeight += $weight;
                    }
                }

                $avgHeightScale /= $totalWeight;
                $avgHeightBase /= $totalWeight;
                $avgHeightScale = $avgHeightScale * 0.9 + 0.1;
                $avgHeightBase = ($avgHeightBase * 4.0 - 1.0) / 8.0;

                $noiseH = $heightNoise[$indexHeight++] / 8000.0;
                if ($noiseH < 0) {
                    $noiseH = -$noiseH * 0.3;
                }

                $noiseH = $noiseH * 3.0 - 2.0;
                if ($noiseH < 0) {
                    $noiseH = max($noiseH * 0.5, -1.0) / 1.4 * 0.5;
                } else {
                    $noiseH = min($noiseH, 1.0) / 8.0;
                }

                $noiseH = ($noiseH * 0.2 + $avgHeightBase) * self::BASE_SIZE / 8.0 * 4.0 + self::BASE_SIZE;
                for ($k = 0; $k < 33; ++$k) {
                    $nh = ($k - $noiseH) * self::STRETCH_Y * 128.0 / 256.0 / $avgHeightScale;
                    if ($nh < 0.0) {
                        $nh *= 4.0;
                    }

                    $noiseR = $roughnessNoise[$index] / 512.0;
                    $noiseR2 = $roughnessNoise2[$index] / 512.0;
                    $noiseD = ($detailNoise[$index] / 10.0 + 1.0) / 2.0;

                    $dens = $noiseD < 0 ? $noiseR : ($noiseD > 1 ? $noiseR2 : $noiseR + ($noiseR2 - $noiseR) * $noiseD);
                    $dens -= $nh;
                    ++$index;
                    if ($k > 29) {
                        $lowering = ($k - 29) / 3.0;
                        $dens = $dens * (1.0 - $lowering) + -10.0 * $lowering;
                    }
                    $density[$this->densityHash($i, $j, $k)] = $dens;
                }
            }
        }
        return $density;
    }

    private function DensityHash(int $i, int $j, int $k): float
    {
        return ($k << 6) | ($j << 3) | $i;
    }

    public function ElevationWeightHash(int $x, int $z): int {
        return ($x << 3) | $z;
    }

    public function __destruct()
    {
        unset($this->elevationWeight);
        unset($this->groundMap);

        $this->mapLayer->highResolution->clear();
        $this->mapLayer->lowResolution->clear();
    }
}