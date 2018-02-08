<?php

namespace VGCore\factory\item\projectile;

use pocketmine\item\Item;
use pocketmine\item\ProjectileItem;

class EnderPearl extends ProjectileItem {
    
    const EP = [
        "EnderPearl",
        1.1,
        8
    ];
    
    public function __construct($meta = 0, $count = 1) {
        parent::__construct(Item::ENDER_PEARL, $meta, $count, self::EP[0]);
    }
    
    public function getProjectileEntityType(): string {
        return self::EP[0];
    }
    
    public function getThrowForce(): float {
        return self::EP[1];
    }
    
    public function getMaxStackSize(): int {
        return self::EP[2];
    }
    
}