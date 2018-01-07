<?php

namespace VGCore\spawner\entity;

use VGCore\spawner\entity\BasicEntity;

class ChickenSpawner extends BasicEntity {
    
    const NETWORK_ID = self::CHICKEN;
    
    public $width = 0.6;
    public $lenght = 0.6;
    public $height = 0;
    
    public function getName(): string {
        return "Chicken";
    }
    
}