<?php

namespace VGCore\lobby\pet\entity;

use VGCore\lobby\pet\ai\PAI;

class PigPet extends PAI {
    
    const NETWORK_ID = 12;
    
    public $width = 0.7;
    public $height = 0.9;
    
    public function getName() {
        return "Pig Pet";
    }
    
}