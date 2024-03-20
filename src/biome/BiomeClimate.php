<?php

namespace JonasWindmann\Giganilla\biome;

use JonasWindmann\Giganilla\GigaRandom;
use JonasWindmann\Giganilla\noise\octave\SimplexOctaveGenerator;
use pocketmine\utils\SingletonTrait;

class BiomeClimate {

    use SingletonTrait;

    public GigaRandom $random;
    public SimplexOctaveGenerator $noiseGen;
    public array $climates = [];
    public array $defaultClimate = ['temperature' => 0.8, 'humidity' => 0.4, 'canRain' => true];

    public function __construct()
    {
        $this->random = new GigaRandom(1234);
        $this->noiseGen = new SimplexOctaveGenerator($this->random, 1, 0, 0, 0);
    }

    public function Get(string $biome) {
        if (array_key_exists($biome, $this->climates)) {
            return $this->climates[$biome];
        }

        return $this->defaultClimate;
    }

    public function GetVariatedTemperature(string $biome, float $x, float $y, float $z) {
        $temp = $this->Get($biome)['temperature'];
        if ($y > 64) {
            $variation = $this->noiseGen->Noise($x, $z, 0, 0.5, 2.0, false) * 4.0;

            return $temp - ($variation + ($y - 64)) * 0.05 / 30.0;
        }

        return $temp;
    }

    public function GetBiomeTemperature(string $biome) {
        return $this->Get($biome)['temperature'];
    }

    public function GetBiomeHumidity(string $biome) {
        return $this->Get($biome)['humidity'];
    }

    public function IsWet(string $biome): bool
    {
        return $this->GetBiomeHumidity($biome) > 0.85;
    }

    public function IsCold(string $biome, float $x, float $y, float $z): bool
    {
        return $this->GetVariatedTemperature($biome, $x, $y, $z) < 0.15;
    }

    public function IsRainy(string $biome, float $x, float $y, float $z): bool
    {
        return $this->Get($biome)['canRain'] && !$this->IsCold($biome, $x, $y, $z);
    }

    public function IsSnowy(string $biome, float $x, float $y, float $z): bool
    {
        return $this->Get($biome)['canRain'] && $this->IsCold($biome, $x, $y, $z);
    }

    public function Init(bool $isUHC): void
    {
        if (!empty($this->climates)) {
            return;
        }

        $this->RegisterBiome(['temperature' => 0.8, 'humidity' => 0.4, 'canRain' => true], [
            'PLAINS', 'SUNFLOWER_PLAINS', 'BEACH'
        ]);
        $this->RegisterBiome(['temperature' => 2.0, 'humidity' => 0.0, 'canRain' => false], [
            'DESERT', 'DESERT_HILLS', 'DESERT_MOUNTAINS', 'MESA', 'MESA_BRYCE', 'MESA_PLATEAU', 'MESA_PLATEAU_FOREST', 'MESA_PLATEAU_MOUNTAINS', 'MESA_PLATEAU_FOREST_MOUNTAINS', 'HELL'
        ]);
        $this->RegisterBiome(['temperature' => 0.2, 'humidity' => 0.3, 'canRain' => true], [
            'EXTREME_HILLS', 'EXTREME_HILLS_PLUS', 'EXTREME_HILLS_MOUNTAINS', 'EXTREME_HILLS_PLUS_MOUNTAINS', 'STONE_BEACH', 'SMALL_MOUNTAINS'
        ]);
        $this->RegisterBiome(['temperature' => 0.7, 'humidity' => 0.8, 'canRain' => true], [
            'FOREST', 'FOREST_HILLS', 'FLOWER_FOREST', 'ROOFED_FOREST', 'ROOFED_FOREST_MOUNTAINS'
        ]);
        $this->RegisterBiome(['temperature' => 0.6, 'humidity' => 0.6, 'canRain' => true], [
            'BIRCH_FOREST', 'BIRCH_FOREST_HILLS', 'BIRCH_FOREST_MOUNTAINS', 'BIRCH_FOREST_HILLS_MOUNTAINS'
        ]);

        if ($isUHC) {
            $this->RegisterBiome(['temperature' => 0.25, 'humidity' => 0.8, 'canRain' => true], [
                'TAIGA', 'TAIGA_HILLS', 'TAIGA_MOUNTAINS'
            ]);
            $this->RegisterBiome(['temperature' => 0.0, 'humidity' => 0.5, 'canRain' => true], [
                'ICE_PLAINS', 'ICE_MOUNTAINS', 'ICE_PLAINS_SPIKES', 'FROZEN_RIVER'
            ]);
        } else {
            $this->RegisterBiome(['temperature' => 0.25, 'humidity' => 0.8, 'canRain' => true], [
                'TAIGA', 'TAIGA_HILLS', 'TAIGA_MOUNTAINS', 'MEGA_SPRUCE_TAIGA', 'MEGA_SPRUCE_TAIGA_HILLS'
            ]);
            $this->RegisterBiome(['temperature' => 0.8, 'humidity' => 0.9, 'canRain' => true], [
                'SWAMPLAND', 'SWAMPLAND_MOUNTAINS'
            ]);
            $this->RegisterBiome(['temperature' => 0.0, 'humidity' => 0.5, 'canRain' => true], [
                'ICE_PLAINS', 'ICE_MOUNTAINS', 'ICE_PLAINS_SPIKES', 'FROZEN_RIVER', 'FROZEN_OCEAN'
            ]);
            $this->RegisterBiome(['temperature' => 0.95, 'humidity' => 0.9, 'canRain' => true], [
                'JUNGLE_HILLS', 'JUNGLE_MOUNTAINS'
            ]);
            $this->RegisterBiome(['temperature' => 0.95, 'humidity' => 0.8, 'canRain' => true], [
                'JUNGLE_EDGE', 'JUNGLE_EDGE_MOUNTAINS'
            ]);
            $this->RegisterBiome(['temperature' => 0.3, 'humidity' => 0.8, 'canRain' => true], [
                'MEGA_TAIGA', 'MEGA_TAIGA_HILLS'
            ]);
        }

        $this->RegisterBiome(['temperature' => 0.9, 'humidity' => 1.0, 'canRain' => true], [
            'MUSHROOM_ISLAND', 'MUSHROOM_SHORE'
        ]);
        $this->RegisterBiome(['temperature' => 0.05, 'humidity' => 0.3, 'canRain' => true], [
            'COLD_BEACH'
        ]);
        $this->RegisterBiome(['temperature' => -0.5, 'humidity' => 0.4, 'canRain' => true], [
            'COLD_TAIGA', 'COLD_TAIGA_HILLS', 'COLD_TAIGA_MOUNTAINS'
        ]);
        $this->RegisterBiome(['temperature' => 1.2, 'humidity' => 0.0, 'canRain' => false], [
            'SAVANNA'
        ]);
        $this->RegisterBiome(['temperature' => 1.1, 'humidity' => 0.0, 'canRain' => false], [
            'SAVANNA_MOUNTAINS'
        ]);
        $this->RegisterBiome(['temperature' => 1.0, 'humidity' => 0.0, 'canRain' => false], [
            'SAVANNA_PLATEAU'
        ]);
        $this->RegisterBiome(['temperature' => 0.5, 'humidity' => 0.0, 'canRain' => false], [
            'SAVANNA_PLATEAU_MOUNTAINS'
        ]);
        $this->RegisterBiome(['temperature' => 0.5, 'humidity' => 0.5, 'canRain' => false], [
            'SKY'
        ]);
    }

    public function RegisterBiome(array $climate, array $biomeIds): void
    {
        foreach ($biomeIds as $biomeId) {
            $this->climates[$biomeId] = $climate;
        }
    }
}
