<?php

namespace JonasWindmann\Giganilla;

class GigaRandom {
    private int $seed;
    private int $x;
    private int $y;
    private int $z;
    private int $w;

    public function __construct(int $iSeed) {
        $this->seed = $iSeed;
        $this->setSeed($this->seed);
    }

    public function setSeed(int $mSeed): void {
        $this->seed = $mSeed;

        $X = 123456789;
        $Y = 362436069;
        $Z = 521288629;
        $W = 88675123;

        $this->x = $X ^ $mSeed;
        $this->y = $Y ^ ($mSeed << 17) | (($mSeed >> 15) & 0x7fffffff) & 0xffffffff;
        $this->z = $Z ^ ($mSeed << 31) | (($mSeed >> 1) & 0x7fffffff) & 0xffffffff;
        $this->w = $W ^ ($mSeed << 18) | (($mSeed >> 14) & 0x7fffffff) & 0xffffffff;
    }

    public function nextSignedInt(): int {
        $t = ($this->x ^ ($this->x << 11)) & 0xffffffff;

        $this->x = $this->y; // PhpStan: "$this->x should probably not be assigned to $this->y" <- ignore
        $this->y = $this->z;
        $this->z = $this->w;
        $this->w = ($this->w ^ (($this->w >> 19) & 0x7fffffff) ^ ($t ^ (($t >> 8) & 0x7fffffff))) & 0xffffffff;

        return $this->w;
    }

    public function nextInt(): int {
        return $this->nextSignedInt() & 0x7fffffff;
    }

    public function nextIntWithBound(int|float $bound): int {
        $intBound = round($bound);

        if($intBound == 0) return 0;

        return $this->nextInt() % (int)$bound;
    }

    public function nextFloat(): float {
        return (float) $this->nextInt() / 2147483647.0;
    }

    public function nextSignedFloat(): float {
        return (float) $this->nextSignedInt() / 2147483647.0;
    }

    public function nextBoolean(): bool {
        return ($this->nextSignedInt() & 0x01) === 0;
    }

    public function nextLong(): int {
        return ($this->nextSignedInt() << 32) | $this->nextSignedInt();
    }

    public function nextRange(int $start, int $end): int {
        return $start + ($this->nextInt() % ($end + 1 - $start));
    }

    public function getSeed(): int {
        return $this->seed;
    }
}
