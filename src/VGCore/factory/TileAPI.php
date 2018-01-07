<?php

namespace VGCore\factory;

use pocketmine\tile\Tile;
// >>>
use VGCore\factory\tile\{
    MobSpawner
};

class TileAPI extends Tile {
    
    const MOB_SPAWNER = "MobSpawner";
    
    public static function start() {
        self::registerTile(MobSpawner::class);
    }
    
}