<?php

namespace VGCore\factory;

use pocketmine\entity\Entity;
// >>>
use VGCore\factory\entity\projectile\EP;

class EntityAPI {
    
    const ENTITY = [
        EP::class    
    ];
    
    const MCNAME = [
        'EnderPearl'
    ];
    
    const MCDATA = [
        'minecraft:enderpearl'
    ];
    
    public static function start() {
        foreach (self::ENTITY as $i => $v) {
            Entity::registerEntity(self::ENTITY[$i], false, [self::MCNAME[$i], self::MCDATA[$i]]);
        }
    }
    
}