<?php

namespace VGCore\spawner\entity;

use pocketmine\entity\Monster;

use pocketmine\item\Item;

abstract class BasicMonster extends Monster {
    
    const LOOT = [];
    
    public abstract function getName(): string;
    
    public function getDrops(): array {
        return self::LOOT;
    }
    
}