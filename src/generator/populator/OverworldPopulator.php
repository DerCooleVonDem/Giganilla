<?php

namespace JonasWindmann\Giganilla\generator\populator;

use JonasWindmann\Giganilla\GigaRandom;
use pocketmine\world\ChunkManager;

class OverworldPopulator
{
    private array $biomePopulators = [];

    public BiomePopulator $defaultPopulator;
    public PlainsPopulator $plainsPopulator;
    public SunflowerPlainsPopulator $sunflowerPlainsPopulator;
    public ForestPopulator $forestPopulator;
    public BirchForestPopulator $birchForestPopulator;
    public BirchForestMountainsPopulator $birchForestMountainsPopulator;
    public RoofedForestPopulator $roofedForestPopulator;
    public FlowerForestPopulator $flowerForestPopulator;
    public DesertPopulator $desertPopulator;
    public DesertMountainsPopulator $desertMountainsPopulator;
    public JunglePopulator $junglePopulator;
    public JungleEdgePopulator $jungleEdgePopulator;
    public SwamplandPopulator $swamplandPopulator;
    public TaigaPopulator $taigaPopulator;
    public MegaTaigaPopulator $megaTaigaPopulator;
    public MegaSpruceTaigaPopulator $megaSpruceTaigaPopulator;
    public IcePlainsPopulator $icePlainsPopulator;
    public IcePlainsSpikesPopulator $icePlainsSpikesPopulator;
    public SavannaPopulator $savannaPopulator;
    public SavannaMountainsPopulator $savannaMountainsPopulator;

    public function __construct()
    {
        $this->defaultPopulator = new BiomePopulator();
        $this->plainsPopulator = new PlainsPopulator();
        $this->sunflowerPlainsPopulator = new SunflowerPlainsPopulator();
        $this->forestPopulator = new ForestPopulator();
        $this->birchForestPopulator = new BirchForestPopulator();
        $this->birchForestMountainsPopulator = new BirchForestMountainsPopulator();
        $this->roofedForestPopulator = new RoofedForestPopulator();
        $this->flowerForestPopulator = new FlowerForestPopulator();
        $this->desertPopulator = new DesertPopulator();
        $this->desertMountainsPopulator = new DesertMountainsPopulator();
        $this->junglePopulator = new JunglePopulator();
        $this->jungleEdgePopulator = new JungleEdgePopulator();
        $this->swamplandPopulator = new SwamplandPopulator();
        $this->taigaPopulator = new TaigaPopulator();
        $this->megaTaigaPopulator = new MegaTaigaPopulator();
        $this->megaSpruceTaigaPopulator = new MegaSpruceTaigaPopulator();
        $this->icePlainsPopulator = new IcePlainsPopulator();
        $this->icePlainsSpikesPopulator = new IcePlainsSpikesPopulator();
        $this->savannaPopulator = new SavannaPopulator();
        $this->savannaMountainsPopulator = new SavannaMountainsPopulator();

        $this->RegisterBiomePopulator($this->defaultPopulator);
        $this->RegisterBiomePopulator($this->plainsPopulator);
        $this->RegisterBiomePopulator($this->sunflowerPlainsPopulator);
        $this->RegisterBiomePopulator($this->forestPopulator);
        $this->RegisterBiomePopulator($this->birchForestPopulator);
        $this->RegisterBiomePopulator($this->birchForestMountainsPopulator);
        $this->RegisterBiomePopulator($this->roofedForestPopulator);
        $this->RegisterBiomePopulator($this->flowerForestPopulator);
        $this->RegisterBiomePopulator($this->desertPopulator);
        $this->RegisterBiomePopulator($this->desertMountainsPopulator);
        $this->RegisterBiomePopulator($this->junglePopulator);
        $this->RegisterBiomePopulator($this->jungleEdgePopulator);
        $this->RegisterBiomePopulator($this->swamplandPopulator);
        $this->RegisterBiomePopulator($this->taigaPopulator);
        $this->RegisterBiomePopulator($this->megaTaigaPopulator);
        $this->RegisterBiomePopulator($this->megaSpruceTaigaPopulator);
        $this->RegisterBiomePopulator($this->icePlainsPopulator);
        $this->RegisterBiomePopulator($this->icePlainsSpikesPopulator);
        $this->RegisterBiomePopulator($this->savannaPopulator);
        $this->RegisterBiomePopulator($this->savannaMountainsPopulator);

        // The commented out populators are not implemented yet
        // ExtremeHillsPopulator
        // ExtremeHillsPlusPopulator
        // MesaPopulator
        // MesaForestPopulator
        // MushroomIslandPopulator
        // OceanPopulator
    }

    public function RegisterBiomePopulator(IPopulator $populator): void
    {
        $populator->InitPopulators();

        foreach ($populator->GetBiomes() as $biome) {
            if (isset($this->biomePopulators[$biome])) {
                unset($this->biomePopulators[$biome]);
            }
            $this->biomePopulators[$biome] = $populator;
        }
    }

    public function Populate(ChunkManager $world, GigaRandom $random, int $chunkX, int $chunkZ): void
    {
        $chunk = $world->GetChunk($chunkX, $chunkZ);
        $subChunk = $chunk->GetSubChunk(0);
        // Notice: We only have to get one slice of the subchunk because we setting the biome at the moment to be the same in all files -> take a look at Giganilla.php
        // TODO: Like I said, this api change has huge potential for three dimensional biomes like a DEEP DARK biome etc.
        $biome = $subChunk->GetBiomeArray()->Get(8, 8);

        if (isset($this->biomePopulators[$biome])) {
            $this->biomePopulators[$biome]->Populate($world, $random, $chunkX, $chunkZ);
        }
    }

    public function __destruct()
    {
        $this->biomePopulators = [];
    }
}