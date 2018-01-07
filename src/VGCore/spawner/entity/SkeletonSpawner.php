<?php

namespace VGCore\spawner\entity;

use VGCore\spawner\entity\BasicMonster;

class SkeletonSpawner extends BasicMonster {
    
    const NETWORK_ID = self::SKELETON;
    
    public $width = 0.6;
    public $lenght = 0.5;
    public $height = 1.8;
    
    public function getName(): string {
        return "Skeleton";
    }
    
}