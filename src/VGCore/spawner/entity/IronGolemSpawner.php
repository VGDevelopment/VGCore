<?php

namespace VGCore\spawner\entity;

use VGCore\spawner\entity\BasicEntity;

class IronGolemSpawner extends BasicEntity {
    
    const NETWORK_ID = self::IRON_GOLEM;
    
    public $width = 0.3;
    public $lenght = 0.9;
    public $height = 2.8;
    
    public function getName(): string {
        return "Iron Golem";
    }
    
    public function initEntity(): void {
        $this->setMaxHealth(100);
        parent::initEntity(100);
    }
    
}