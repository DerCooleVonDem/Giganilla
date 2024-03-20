<?php

namespace JonasWindmann\Giganilla\generator\populator;

use JonasWindmann\Giganilla\biome\BiomeList;
use JonasWindmann\Giganilla\generator\populator\decorator\objects\BigOakTree;
use JonasWindmann\Giganilla\generator\populator\decorator\objects\GenericTree;
use pocketmine\block\VanillaBlocks;

class BiomePopulator implements IPopulator {

    private BigOakTree $bigOakTree;
    private GenericTree $genericTree;

    private WaterLakeDecorator $waterLakeDecorator;
    private LavaLakeDecorator $lavaLakeDecorator;
    private SandPatchDecorator $sandPatchDecorator;
    private ClayPatchDecorator $clayPatchDecorator;
    private GravelPatchDecorator $gravelPatchDecorator;
    private DoublePlantDecorator $doublePlantDecorator;
    private TreeDecorator $treeDecorator;
    private FlowerDecorator $flowerDecorator;
    private TallGrassDecorator $tallGrassDecorator;
    private DeadBushDecorator $deadBushDecorator;
    private BrownMushroomDecorator $brownMushroomDecorator;
    private RedMushroomDecorator $redMushroomDecorator;
    private SugarCaneDecorator $sugarCaneDecorator;
    private PumpkinDecorator $pumpkinDecorator;
    private CactusDecorator $cactusDecorator;
    private OreDecorator $orePopulator;

    public function __construct() {
        $this->bigOakTree = new BigOakTree();
        $this->genericTree = new GenericTree();

        $this->waterLakeDecorator = new WaterLakeDecorator();
        $this->lavaLakeDecorator = new LavaLakeDecorator();
        $this->sandPatchDecorator = new SandPatchDecorator();
        $this->clayPatchDecorator = new ClayPatchDecorator();
        $this->gravelPatchDecorator = new GravelPatchDecorator();
        $this->doublePlantDecorator = new DoublePlantDecorator();
        $this->treeDecorator = new TreeDecorator();
        $this->flowerDecorator = new FlowerDecorator();
        $this->tallGrassDecorator = new TallGrassDecorator();
        $this->deadBushDecorator = new DeadBushDecorator();
        $this->brownMushroomDecorator = new BrownMushroomDecorator();
        $this->redMushroomDecorator = new RedMushroomDecorator();
        $this->sugarCaneDecorator = new SugarCaneDecorator();
        $this->pumpkinDecorator = new PumpkinDecorator();
        $this->cactusDecorator = new CactusDecorator();
        $this->orePopulator = new OreDecorator();

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

    public function Populate($world, $random, $chunkX, $chunkZ): void
    {
        $this->waterLakeDecorator->Populate($world, $random, $chunkX, $chunkZ);
        $this->lavaLakeDecorator->Populate($world, $random, $chunkX, $chunkZ);
        $this->orePopulator->Populate($world, $random, $chunkX, $chunkZ);
        $this->sandPatchDecorator->Populate($world, $random, $chunkX, $chunkZ);
        $this->clayPatchDecorator->Populate($world, $random, $chunkX, $chunkZ);
        $this->gravelPatchDecorator->Populate($world, $random, $chunkX, $chunkZ);

        $this->OnGroundPopulation($world, $random, $chunkX, $chunkZ);
    }

    public function OnGroundPopulation($world, $random, $chunkX, $chunkZ): void
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