<?php

namespace VGCore\lobby\pet\entity;

use VGCore\lobby\pet\FlyingPet;
use VGCore\lobby\pet\int\SmallPet;

class CowPet extends FlyingPet implements SmallPet {

    public $networkid = 11;
    public $name = "Cow Pet";
    public $width = 0.9;
    public $height = 1.3;

}
