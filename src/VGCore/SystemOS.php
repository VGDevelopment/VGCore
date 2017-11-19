<?php

namespace VGCore;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;

use pocketmine\Player;

use pocketmine\plugin\PluginBase;

use pocketmine\utils\Config;
use pocketmine\utils\TextFormat as Chat;

// >>>>>>>>>>>>>>>>>>>>>>>>>>>>

use VGCore\chat\Filter;

use VGCore\listener\ChatFilterListener;

class SystemOS extends PluginBase {
    
    // Base File for arranging everything in good order. This is how every good core should be done. 
    
    public function onEnable() {
        $this->saveDefaultConfig();
        
        $this->getServer()->getPluginManager()->registerEvents(new ChatFilterListener($this), $this);
    }
}