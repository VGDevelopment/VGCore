<?php

namespace VGCore\spawner\entity;

use VGCore\spawner\entity\BasicMonster;

class SpiderSpawner extends BasicMonster {
    
    const NETWORK_ID = self::SPIDER;
    
    public $width = 0.3;
    public $lenght = 0.9;
    public $height = 1.9;
    
    public function getName(): string {
        return "Spider";
    }
    
}