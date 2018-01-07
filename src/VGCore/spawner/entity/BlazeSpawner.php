<?php

namespace VGCore\spawner\entity;

use VGCore\spawner\entity\BasicMonster;

class BlazeSpawner extends BasicMonster {
    
    const NETWORK_ID = self::BLAZE;
    
    public $width = 0.3;
    public $lenght = 0.9;
    public $height = 1.8;
    
    public function getName(): string {
        return "Blaze";
    }
    
}