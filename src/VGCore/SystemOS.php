<?php

namespace VGCore;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;

use pocketmine\Player;

use pocketmine\plugin\PluginBase;

use pocketmine\utils\Config;
use pocketmine\utils\TextFormat as Chat;

use pocketmine\network\mcpe\protocol\PacketPool;
// >>>
use VGCore\economy\PlayerData;

use VGCore\gui\UILoader;

use VGCore\chat\Filter;

use VGCore\listener\ChatFilterListener;
use VGCore\listener\GUIListener;

use VGCore\network\ModalFormRequestPacket;
use VGCore\network\ModalFormResponsePacket;
use VGCore\network\ServerSettingsRequestPacket;
use VGCore\network\ServerSettingsResponsePacket;

class SystemOS extends PluginBase {
    
    // Base File for arranging everything in good order. This is how every good core should be done. 
    
    public function onEnable() {
        $this->getLogger()->info("Starting Virtual Galaxy Operating System (SystemOS)... Loading start.")
        
        self::$instance = $this;
        
        $this->saveDefaultConfig();
        
        // Filter::loadEnable($this);
        // $this->getLogger()->info("Loading Virtual Galaxy Chat Filter...");
        UILoader::loadEnable($this);
        $this->getLogger()->info("Loading Virtual Galaxy Graphical User Interface System...");
    }
}