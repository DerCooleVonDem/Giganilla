<?php

namespace JonasWindmann\Giganilla\generator;

use JonasWindmann\Giganilla\biome\BiomeClimate;
use JonasWindmann\Giganilla\biome\BiomeList;
use JonasWindmann\Giganilla\generator\ground\DirtAndStonePatchGroundGenerator;
use JonasWindmann\Giganilla\generator\ground\DirtPatchGroundGenerator;
use JonasWindmann\Giganilla\generator\ground\GravelPatchGroundGenerator;
use JonasWindmann\Giganilla\generator\ground\GroundGenerator;
use JonasWindmann\Giganilla\generator\ground\MesaGroundGenerator;
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
    private array $mapLayer;
    private GigaRandom $ownRandom; // $random
    private GigaRandom $octaveRandom;
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

        $this->populators = new OverworldPopulator();

        $this->isUHC = self::IS_UHC;
        // Initialize other properties here
        // For example:
        $this->ownRandom = new GigaRandom($seed);
        $this->octaveRandom = new GigaRandom($seed);
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

    }

    public function populateChunk(ChunkManager $world, int $chunkX, int $chunkZ): void
    {

    }

    private function GenerateTerrainDensity(int $x, int $z): array
    {
        // Implement terrain density generation logic here
        return []; // Return an associative array representing TerrainDensity
    }

    private function GenerateChunkData(ChunkManager $world, int $x, int $z, int $biome) {
        // Implement chunk data generation logic here
    }

    private function GenerateRawTerrain(ChunkManager $world, int $x, int $z) {
        // Implement raw terrain generation logic here
    }

    // TODO: Types!!!!
    private function DensityHash($i, $j, $k): float
    {
        // Implement density hash logic here
        return 0; // Placeholder return value
    }

    public function ElevationWeightHash(int $x, int $z): int {
        return ($x << 3) | $z;
    }
}