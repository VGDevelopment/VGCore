<?php

namespace VGCore\spawner;

use pocketmine\entity\Entity;
// >>>
use VGCore\SystemOS;

class SpawnerAPI extends Entity {
    
    public static $mobtype = [
        10 => "Chicken",
        11 => "Cow",
        12 => "Pig",
        13 => "Sheep",
        16 => "Mooshroom",
        20 => "Iron_Golem",
        22 => "Ocelot",
        32 => "Zombie",
        34 => "Skeleton",
        35 => "Spider",
        36 => "Zombie_PigMan",
        43 => "Blaze",
    ];
    
    public static function start(): void {
        // 
    }
    
}