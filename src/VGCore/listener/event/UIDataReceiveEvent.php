<?php

namespace VGCore\listener\event;

use pocketmine\network\mcpe\protocol\DataPacket;
use pocketmine\Player;
use pocketmine\plugin\Plugin;

class UIDataReceiveEvent extends UIEvent {
    
    public static $handlerList = null;
    
    public function __construct(Plugin $plugin, DataPacket $packet, Player $player) {
		parent::__construct($plugin, $packet, $player);
	}
	
	public function getData() {
		return json_decode($this->packet->formData); // function to get the decoded data. Means no manual decoding every fucking time. :)
	}
	
	public function getDataEncoded() {
		return $this->packet->formData; // function to get encoded data (json) incase one needs it. No fucking idea why but enjoy. 
	}
    
}