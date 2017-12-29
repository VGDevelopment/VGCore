<?php

namespace VGCore\lobby\pet\entity;

use VGCore\lobby\pet\ai\PAI;

class BabyZombiePet extends PAI {
    
    const NETWORK_ID = 32;
    
    public $width = 0.36;
	public $height = 0.9;
	
	public function getName(): string {
		return "BabyZombie Pet";
	}
    
}