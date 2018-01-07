<?php

namespace VGCore\spawner\entity;

use VGCore\spawner\entity\BasicEntity;

class MooshroomSpawner extends BasicEntity {
    
    const NETWORK_ID = self::MOOSHROOM;
    
    public $width = 0.3;
    public $lenght = 0.9;
    public $height = 1.8;
    
    public function getName(): string {
        return "Mooshroom";
    }
    
}