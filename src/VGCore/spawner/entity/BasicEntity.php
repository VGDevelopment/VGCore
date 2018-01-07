<?php

namespace VGCore\spawner\entity;

use pocketmine\entity\Animal;

use pocketmine\item\Item;

abstract class BasicEntity extends Animal {
    
    const LOOT = [];
    
    public abstract function getName(): string;
    
    public function getDrops(): array {
        return self::LOOT;
    }
    
}