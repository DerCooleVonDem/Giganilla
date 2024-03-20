<?php

namespace JonasWindmann\Giganilla\biome;

class BiomeHeightManager {
    public static array $heights = [];
    public static array $defaultHeight = [0.1, 0.2];


    public static function Get(int $biome) {
        if (array_key_exists($biome, self::$heights)) {
            return self::$heights[$biome];
        }

        return self::$defaultHeight;
    }

    public static function RegisterBiome(array $climate, array $biomeIds): void
    {
        foreach ($biomeIds as $biomeId) {
            self::$heights[$biomeId] = $climate;
        }
    }

    public static function Init(bool $isUHC): void
    {
        if ($isUHC) {
            self::RegisterBiome([0.2, 0.2], [BiomeList::TAIGA, BiomeList::COLD_TAIGA, BiomeList::MEGA_TAIGA]);
            self::RegisterBiome([0.45, 0.3], [BiomeList::ICE_MOUNTAINS, BiomeList::DESERT_HILLS, BiomeList::FOREST_HILLS, BiomeList::TAIGA_HILLS, BiomeList::SMALL_MOUNTAINS, BiomeList::BIRCH_FOREST_HILLS, BiomeList::COLD_TAIGA_HILLS, BiomeList::MESA_PLATEAU_FOREST_MOUNTAINS, BiomeList::MESA_PLATEAU_MOUNTAINS]);
            self::RegisterBiome([0.2, 0.4], [BiomeList::BIRCH_FOREST_MOUNTAINS, BiomeList::ROOFED_FOREST_MOUNTAINS]);
            self::RegisterBiome([0.3, 0.4], [BiomeList::TAIGA_MOUNTAINS, BiomeList::COLD_TAIGA_MOUNTAINS]);
        } else {
            self::RegisterBiome([-1.0, 0.1], [BiomeList::OCEAN, BiomeList::FROZEN_OCEAN]);
            self::RegisterBiome([-1.8, 0.1], [BiomeList::DEEP_OCEAN]);
            self::RegisterBiome([0.2, 0.2], [BiomeList::TAIGA, BiomeList::COLD_TAIGA, BiomeList::MEGA_TAIGA]);
            self::RegisterBiome([-0.2, 0.1], [BiomeList::SWAMPLAND]);
            self::RegisterBiome([0.45, 0.3], [BiomeList::ICE_MOUNTAINS, BiomeList::DESERT_HILLS, BiomeList::FOREST_HILLS, BiomeList::TAIGA_HILLS, BiomeList::SMALL_MOUNTAINS, BiomeList::JUNGLE_HILLS, BiomeList::BIRCH_FOREST_HILLS, BiomeList::COLD_TAIGA_HILLS, BiomeList::MEGA_TAIGA_HILLS, BiomeList::MESA_PLATEAU_FOREST_MOUNTAINS, BiomeList::MESA_PLATEAU_MOUNTAINS]);
            self::RegisterBiome([-0.1, 0.3], [BiomeList::SWAMPLAND_MOUNTAINS]);
            self::RegisterBiome([0.2, 0.4], [BiomeList::JUNGLE_MOUNTAINS, BiomeList::JUNGLE_EDGE_MOUNTAINS, BiomeList::BIRCH_FOREST_MOUNTAINS, BiomeList::ROOFED_FOREST_MOUNTAINS]);
            self::RegisterBiome([0.3, 0.4], [BiomeList::TAIGA_MOUNTAINS, BiomeList::COLD_TAIGA_MOUNTAINS, BiomeList::MEGA_SPRUCE_TAIGA, BiomeList::MEGA_SPRUCE_TAIGA_HILLS]);
        }

        self::RegisterBiome([-0.5, 0.0], [BiomeList::RIVER, BiomeList::FROZEN_RIVER]);
        self::RegisterBiome([0.0, 0.025], [BiomeList::BEACH, BiomeList::COLD_BEACH, BiomeList::MUSHROOM_SHORE]);
        self::RegisterBiome([0.1, 0.8], [BiomeList::STONE_BEACH]);
        self::RegisterBiome([0.125, 0.05], [BiomeList::DESERT, BiomeList::ICE_PLAINS, BiomeList::SAVANNA]);

        self::RegisterBiome([1.0, 0.5], [BiomeList::EXTREME_HILLS, BiomeList::EXTREME_HILLS_PLUS, BiomeList::EXTREME_HILLS_MOUNTAINS, BiomeList::EXTREME_HILLS_PLUS_MOUNTAINS]);
        self::RegisterBiome([0.2, 0.3], [BiomeList::MUSHROOM_ISLAND]);

        self::RegisterBiome([1.5, 0.025], [BiomeList::SAVANNA_PLATEAU, BiomeList::MESA_PLATEAU_FOREST, BiomeList::MESA_PLATEAU]);
        self::RegisterBiome([0.275, 0.25], [BiomeList::DESERT_MOUNTAINS]);
        self::RegisterBiome([0.525, 0.55], [BiomeList::ICE_PLAINS_SPIKES]);
        self::RegisterBiome([0.55, 0.5], [BiomeList::BIRCH_FOREST_HILLS_MOUNTAINS]);
        self::RegisterBiome([0.1, 0.4], [BiomeList::FLOWER_FOREST]);
        self::RegisterBiome([0.4125, 1.325], [BiomeList::SAVANNA_MOUNTAINS]);
        self::RegisterBiome([1.1, 1.3125], [BiomeList::SAVANNA_PLATEAU_MOUNTAINS]);
    }
}