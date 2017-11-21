<?php

namespace VGCore\listener\event;

use pocketmine\network\mcpe\protocol\DataPacket;
use pocketmine\Player;
use pocketmine\plugin\Plugin;

class UICloseEvent extends UIEvent {
    
    public static $handlerList = null;
    
    public function __construct(Plugin $plugin, DataPacket $packet, Player $player) {
		parent::__construct($plugin, $packet, $player);
	}
    
}