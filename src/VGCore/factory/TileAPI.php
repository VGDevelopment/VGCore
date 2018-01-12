<?php

namespace VGCore\factory;

use pocketmine\tile\Tile;
// >>>
use VGCore\factory\tile\{
    MobSpawner
};

class TileAPI {
    
    const MOB_SPAWNER = "MobSpawner";
    
    public static function start() {
        Tile::registerTile(MobSpawner::class);
    }
    
}