<?php

namespace VGCore\spawner\entity;

use VGCore\spawner\entity\BasicMonster;

class ZombiePigmanSpawner extends BasicMonster {
    
    const NETWORK_ID = self::ZOMBIE_PIGMAN;
    
    public $width = 0.6;
    public $lenght = 0.6;
    public $height = 1.8;
    
    public function getName(): string {
        return "Zombie Pigman";
    }
    
}