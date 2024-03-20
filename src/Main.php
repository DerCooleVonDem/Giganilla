<?php

declare(strict_types=1);

namespace JonasWindmann\Giganilla;

use JonasWindmann\Giganilla\generator\Giganilla;
use pocketmine\plugin\PluginBase;
use pocketmine\world\generator\GeneratorManager;

class Main extends PluginBase{

    // Something that i just lerned, bc i had a lot off time to do so
    // BlockTypeIds are just the basic form of a block (could be a closed and at the same time open door)
    // BlockStateIds are the complete full representation of a block (closed door, open door, etc.)
    // So when using block state then it makes a difference if a door is open and closed
    // When using block type then it does not make a difference
    // I understand now lol

    public function onLoad(): void
    {
        GeneratorManager::getInstance()->addGenerator(Giganilla::class, "giganilla", fn () => null);
        $this->getLogger()->info("(Dev) Registered the generator");
    }

    public function onEnable(): void
    {
    }
}
