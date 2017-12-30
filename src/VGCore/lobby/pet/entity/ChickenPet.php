<?php

namespace VGCore\lobby\pet\entity;

use VGCore\lobby\pet\WalkingPet;
use VGCore\lobby\pet\int\SmallPet;

class ChickenPet extends WalkingPet implements SmallPet {
    
    public $networkid = 10;
    public $name = "Chicken Pet";
    public $width = 0.4;
    public $height = 0.7;
    
}