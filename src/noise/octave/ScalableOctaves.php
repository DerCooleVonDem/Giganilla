<?php

namespace JonasWindmann\Giganilla\noise\octave;

class ScalableOctaves {
    private float $xScale;
    private float $yScale;
    private float $zScale;

    public function SetScale(float $scale): void
    {
        $this->SetXScale($scale);
        $this->SetYScale($scale);
        $this->SetZScale($scale);
    }

    public function GetXScale(): float {
        return $this->xScale;
    }

    public function GetYScale(): float {
        return $this->yScale;
    }

    public function GetZScale(): float {
        return $this->zScale;
    }

    public function SetXScale(float $scaleX): void
    {
        $this->xScale = $scaleX;
    }

    public function SetYScale(float $scaleY): void
    {
        $this->yScale = $scaleY;
    }

    public function SetZScale(float $scaleZ): void
    {
        $this->zScale = $scaleZ;
    }
}
