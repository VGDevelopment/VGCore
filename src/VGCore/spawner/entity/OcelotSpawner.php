<?php

namespace VGCore\spawner\entity;

use VGCore\spawner\entity\BasicEntity;

class OcelotSpawner extends BasicEntity {
    
    const NETWORK_ID = self::OCELOT;
    
    public $width = 0.312;
    public $lenght = 2.188;
    public $height = 0.75;
    
    public function getName(): string {
        return "Ocelot";
    }
    
}