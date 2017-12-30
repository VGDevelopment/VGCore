<?php

namespace VGCore\lobby\pet\entity;

use VGCore\lobby\pet\WalkingPet;
use VGCore\lobby\pet\int\SmallPet;

class WolfPet extends WalkingPet implements SmallPet {
    
    public $networkid = 14;
    public $name = "Wolf Pet";
    public $width = 0.72;
    public $height = 0.9;
    
    public function generateCustomData(): void {
        $random = mt_rand(0, 15);
        $this->setDataProperty(self::DATA_COLOUR, self::DATA_TYPE_BYTE, $random);
    }
    
}