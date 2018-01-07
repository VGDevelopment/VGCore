<?php

namespace VGCore\spawner\entity;

use VGCore\spawner\entity\BasicEntity;

class CowSpawner extends BasicEntity {
    
    const NETWORK_ID = self::COW;
    
    public $width = 0.3;
    public $lenght = 0.9;
    public $height = 0;
    
    public function getName(): string {
        return "Cow";
    }
    
}