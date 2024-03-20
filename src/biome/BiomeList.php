<?php

namespace JonasWindmann\Giganilla\biome;

class BiomeList {
    const OCEAN = 0;
    const PLAINS = 1;
    const DESERT = 2;
    const EXTREME_HILLS = 3;
    const FOREST = 4;
    const TAIGA = 5;
    const SWAMPLAND = 6;
    const RIVER = 7;
    const HELL = 8;
    const SKY = 9;
    const FROZEN_OCEAN = 10;
    const FROZEN_RIVER = 11;
    const ICE_PLAINS = 12;
    const ICE_MOUNTAINS = 13;
    const MUSHROOM_ISLAND = 14;
    const MUSHROOM_SHORE = 15;
    const BEACH = 16;
    const DESERT_HILLS = 17;
    const FOREST_HILLS = 18;
    const TAIGA_HILLS = 19;
    const SMALL_MOUNTAINS = 20;
    const JUNGLE = 21;
    const JUNGLE_HILLS = 22;
    const JUNGLE_EDGE = 23;
    const DEEP_OCEAN = 24;
    const STONE_BEACH = 25;
    const COLD_BEACH = 26;
    const BIRCH_FOREST = 27;
    const BIRCH_FOREST_HILLS = 28;
    const ROOFED_FOREST = 29;
    const COLD_TAIGA = 30;
    const COLD_TAIGA_HILLS = 31;
    const MEGA_TAIGA = 32;
    const MEGA_TAIGA_HILLS = 33;
    const EXTREME_HILLS_PLUS = 34;
    const SAVANNA = 35;
    const SAVANNA_PLATEAU = 36;
    const MESA = 37;
    const MESA_PLATEAU_FOREST = 38;
    const MESA_PLATEAU = 39;
    const SUNFLOWER_PLAINS = 129;
    const DESERT_MOUNTAINS = 130;
    const EXTREME_HILLS_MOUNTAINS = 131;
    const FLOWER_FOREST = 132;
    const TAIGA_MOUNTAINS = 133;
    const SWAMPLAND_MOUNTAINS = 134;
    const ICE_PLAINS_SPIKES = 140;
    const JUNGLE_MOUNTAINS = 149;
    const JUNGLE_EDGE_MOUNTAINS = 151;
    const BIRCH_FOREST_MOUNTAINS = 155;
    const BIRCH_FOREST_HILLS_MOUNTAINS = 156;
    const ROOFED_FOREST_MOUNTAINS = 157;
    const COLD_TAIGA_MOUNTAINS = 158;
    const MEGA_SPRUCE_TAIGA = 160;
    const MEGA_SPRUCE_TAIGA_HILLS = 161;
    const EXTREME_HILLS_PLUS_MOUNTAINS = 162;
    const SAVANNA_MOUNTAINS = 163;
    const SAVANNA_PLATEAU_MOUNTAINS = 164;
    const MESA_BRYCE = 165;
    const MESA_PLATEAU_FOREST_MOUNTAINS = 166;
    const MESA_PLATEAU_MOUNTAINS = 167;

    // Define an array of all biomes -> needs to be an array static property because php won't allow to use a constant in a constant
    public static array $ALL_BIOMES = [
        self::OCEAN,
        self::PLAINS,
        self::DESERT,
        self::EXTREME_HILLS,
        self::FOREST,
        self::TAIGA,
        self::SWAMPLAND,
        self::RIVER,
        self::HELL,
        self::SKY,
        self::FROZEN_OCEAN,
        self::FROZEN_RIVER,
        self::ICE_PLAINS,
        self::ICE_MOUNTAINS,
        self::MUSHROOM_ISLAND,
        self::MUSHROOM_SHORE,
        self::BEACH,
        self::DESERT_HILLS,
        self::FOREST_HILLS,
        self::TAIGA_HILLS,
        self::SMALL_MOUNTAINS,
        self::JUNGLE,
        self::JUNGLE_HILLS,
        self::JUNGLE_EDGE,
        self::DEEP_OCEAN,
        self::STONE_BEACH,
        self::COLD_BEACH,
        self::BIRCH_FOREST,
        self::BIRCH_FOREST_HILLS,
        self::ROOFED_FOREST,
        self::COLD_TAIGA,
        self::COLD_TAIGA_HILLS,
        self::MEGA_TAIGA,
        self::MEGA_TAIGA_HILLS,
        self::EXTREME_HILLS_PLUS,
        self::SAVANNA,
        self::SAVANNA_PLATEAU,
        self::MESA,
        self::MESA_PLATEAU_FOREST,
        self::MESA_PLATEAU,
        self::SUNFLOWER_PLAINS,
        self::DESERT_MOUNTAINS,
        self::EXTREME_HILLS_MOUNTAINS,
        self::FLOWER_FOREST,
        self::TAIGA_MOUNTAINS,
        self::SWAMPLAND_MOUNTAINS,
        self::ICE_PLAINS_SPIKES,
        self::JUNGLE_MOUNTAINS,
        self::JUNGLE_EDGE_MOUNTAINS,
        self::BIRCH_FOREST_MOUNTAINS,
        self::BIRCH_FOREST_HILLS_MOUNTAINS,
        self::ROOFED_FOREST_MOUNTAINS,
        self::COLD_TAIGA_MOUNTAINS,
        self::MEGA_SPRUCE_TAIGA,
        self::MEGA_SPRUCE_TAIGA_HILLS,
        self::EXTREME_HILLS_PLUS_MOUNTAINS,
        self::SAVANNA_MOUNTAINS,
        self::SAVANNA_PLATEAU_MOUNTAINS,
        self::MESA_BRYCE,
        self::MESA_PLATEAU_FOREST_MOUNTAINS,
        self::MESA_PLATEAU_MOUNTAINS
    ];
}