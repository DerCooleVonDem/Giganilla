<?php

namespace JonasWindmann\Giganilla\generator\populator;

use JonasWindmann\Giganilla\biome\BiomeList;
use JonasWindmann\Giganilla\generator\populator\decorator\CactusDecorator;
use JonasWindmann\Giganilla\generator\populator\decorator\DeadBushDecorator;
use JonasWindmann\Giganilla\generator\populator\decorator\DoublePlantDecorator;
use JonasWindmann\Giganilla\generator\populator\decorator\FlowerDecorator;
use JonasWindmann\Giganilla\generator\populator\decorator\LakeDecorator;
use JonasWindmann\Giganilla\generator\populator\decorator\MushroomDecorator;
use JonasWindmann\Giganilla\generator\populator\decorator\objects\BigOakTree;
use JonasWindmann\Giganilla\generator\populator\decorator\objects\GenericTree;
use JonasWindmann\Giganilla\generator\populator\decorator\PumpkinDecorator;
use JonasWindmann\Giganilla\generator\populator\decorator\SugarCaneDecorator;
use JonasWindmann\Giganilla\generator\populator\decorator\TallGrassDecorator;
use JonasWindmann\Giganilla\generator\populator\decorator\TreeDecorator;
use JonasWindmann\Giganilla\generator\populator\decorator\UnderwaterDecorator;
use JonasWindmann\Giganilla\GigaRandom;
use pocketmine\block\VanillaBlocks;
use pocketmine\world\ChunkManager;

class BiomePopulator implements IPopulator {

    protected BigOakTree $bigOakTree;
    private GenericTree $genericTree;

    protected LakeDecorator $waterLakeDecorator;
    private LakeDecorator $lavaLakeDecorator;
    private UnderwaterDecorator $sandPatchDecorator;
    private UnderwaterDecorator $clayPatchDecorator;
    private UnderwaterDecorator $gravelPatchDecorator;
    protected DoublePlantDecorator $doublePlantDecorator;
    protected TreeDecorator $treeDecorator;
    protected FlowerDecorator $flowerDecorator;
    protected TallGrassDecorator $tallGrassDecorator;
    protected DeadBushDecorator $deadBushDecorator;
    private MushroomDecorator $brownMushroomDecorator;
    private MushroomDecorator $redMushroomDecorator;
    protected SugarCaneDecorator $sugarCaneDecorator;
    private PumpkinDecorator $pumpkinDecorator;
    protected CactusDecorator $cactusDecorator;
    private OrePopulator $orePopulator;

    public function __construct() {
        $this->bigOakTree = new BigOakTree();
        $this->genericTree = new GenericTree();

        $this->waterLakeDecorator = new LakeDecorator(VanillaBlocks::WATER(), 4);
        $this->lavaLakeDecorator = new LakeDecorator(VanillaBlocks::LAVA(), 8, 8);
        $this->sandPatchDecorator = new UnderwaterDecorator(VanillaBlocks::SAND());
        $this->clayPatchDecorator = new UnderwaterDecorator(VanillaBlocks::CLAY());
        $this->gravelPatchDecorator = new UnderwaterDecorator(VanillaBlocks::GRAVEL());
        $this->doublePlantDecorator = new DoublePlantDecorator();
        $this->treeDecorator = new TreeDecorator();
        $this->flowerDecorator = new FlowerDecorator();
        $this->tallGrassDecorator = new TallGrassDecorator();
        $this->deadBushDecorator = new DeadBushDecorator();
        $this->brownMushroomDecorator = new MushroomDecorator(VanillaBlocks::BROWN_MUSHROOM());
        $this->redMushroomDecorator = new MushroomDecorator(VanillaBlocks::RED_MUSHROOM());
        $this->sugarCaneDecorator = new SugarCaneDecorator();
        $this->pumpkinDecorator = new PumpkinDecorator();
        $this->cactusDecorator = new CactusDecorator();
        $this->orePopulator = new OrePopulator();

        $this->InitPopulators();
    }

    public function InitPopulators(): void
    {
        $this->waterLakeDecorator->SetAmount(1);
        $this->lavaLakeDecorator->SetAmount(1);
        $this->sandPatchDecorator->SetAmount(3);
        $this->sandPatchDecorator->SetRadii(7, 2);
        $this->sandPatchDecorator->SetOverridableBlocks([
            VanillaBlocks::DIRT(),
            VanillaBlocks::GRASS()
        ]);
        $this->clayPatchDecorator->SetAmount(1);
        $this->clayPatchDecorator->SetRadii(4, 1);
        $this->clayPatchDecorator->SetOverridableBlocks([
            VanillaBlocks::DIRT()
        ]);
        $this->gravelPatchDecorator->SetAmount(1);
        $this->gravelPatchDecorator->SetRadii(6, 2);
        $this->gravelPatchDecorator->SetOverridableBlocks([
            VanillaBlocks::DIRT(),
            VanillaBlocks::GRASS()
        ]);

        // Follows GlowstoneMC's populators object values.
        $this->doublePlantDecorator->SetAmount(0);
        $this->treeDecorator->SetAmount(PHP_INT_MIN);
        $this->treeDecorator->SetTrees([
            [1, $this->bigOakTree],
            [9, $this->genericTree]
        ]);
        $this->flowerDecorator->SetAmount(2);
        $this->flowerDecorator->SetFlowers([
            [2, VanillaBlocks::DANDELION()],
            [1, VanillaBlocks::POPPY()]
        ]);
        $this->tallGrassDecorator->SetAmount(1);
        $this->deadBushDecorator->SetAmount(0);
        $this->brownMushroomDecorator->SetAmount(1);
        $this->brownMushroomDecorator->SetDensity(0.25);
        $this->redMushroomDecorator->SetAmount(1);
        $this->redMushroomDecorator->SetDensity(0.125);
        $this->sugarCaneDecorator->SetAmount(10);
        $this->cactusDecorator->SetAmount(0);
    }

    public function GetBiomes(): array
    {
        return BiomeList::$ALL_BIOMES;
    }

    public function Populate(ChunkManager $world, GigaRandom $random, int $chunkX, int $chunkZ): void
    {
        $this->waterLakeDecorator->Populate($world, $random, $chunkX, $chunkZ);
        $this->lavaLakeDecorator->Populate($world, $random, $chunkX, $chunkZ);
        $this->orePopulator->Populate($world, $random, $chunkX, $chunkZ);
        $this->sandPatchDecorator->Populate($world, $random, $chunkX, $chunkZ);
        $this->clayPatchDecorator->Populate($world, $random, $chunkX, $chunkZ);
        $this->gravelPatchDecorator->Populate($world, $random, $chunkX, $chunkZ);

        $this->OnGroundPopulation($world, $random, $chunkX, $chunkZ);
    }

    public function OnGroundPopulation(ChunkManager $world, GigaRandom $random, int $chunkX, int $chunkZ): void
    {
        $this->doublePlantDecorator->Populate($world, $random, $chunkX, $chunkZ);
        $this->treeDecorator->Populate($world, $random, $chunkX, $chunkZ);
        $this->flowerDecorator->Populate($world, $random, $chunkX, $chunkZ);
        $this->tallGrassDecorator->Populate($world, $random, $chunkX, $chunkZ);
        $this->deadBushDecorator->Populate($world, $random, $chunkX, $chunkZ);
        $this->brownMushroomDecorator->Populate($world, $random, $chunkX, $chunkZ);
        $this->redMushroomDecorator->Populate($world, $random, $chunkX, $chunkZ);
        $this->sugarCaneDecorator->Populate($world, $random, $chunkX, $chunkZ);
        $this->pumpkinDecorator->Populate($world, $random, $chunkX, $chunkZ);
        $this->cactusDecorator->Populate($world, $random, $chunkX, $chunkZ);
    }
}