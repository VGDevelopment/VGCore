<?php

namespace VGCore\lobby\pet\entity;

use VGCore\lobby\pet\ai\PetAI;

class WolfPet extends PetAI {
    
    const NETWORK_ID = 14;
    
    public $width = 0.72;
	public $height = 0.9;
	
	public function getName() {
		return "WolfPet";
	}
    
}