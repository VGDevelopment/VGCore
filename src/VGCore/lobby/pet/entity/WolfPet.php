<?php

namespace VGCore\lobby\pet\entity;

use VGCore\lobby\pet\ai\PAI;

class WolfPet extends PAI {
    
    const NETWORK_ID = 14;
    
    public $width = 0.72;
	public $height = 0.9;
	
	public function getName(): string {
		return "Wolf Pet";
	}
    
}