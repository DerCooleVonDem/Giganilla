<?php

namespace JonasWindmann\Giganilla\generator\octave;

use JonasWindmann\Giganilla\noise\octave\PerlinOctaveGenerator;
use JonasWindmann\Giganilla\noise\octave\SimplexOctaveGenerator;

class WorldOctaves
{
    public PerlinOctaveGenerator $height;
    public PerlinOctaveGenerator $roughness;
    public PerlinOctaveGenerator $roughness2;
    public PerlinOctaveGenerator $detail;
    public SimplexOctaveGenerator $surface;
}