<?php

namespace VGCore\lobby\pet\entity;

use VGCore\lobby\pet\FlyingPet;
use VGCore\lobby\pet\int\SmallPet;

class ChickenPet extends FlyingPet implements SmallPet {
    
    public $networkid = 10;
    public $name = "Chicken Pet";
    public $width = 0.4;
    public $height = 0.7;
    
}