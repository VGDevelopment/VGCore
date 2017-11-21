<?php

namespace VGCore;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;

use pocketmine\Player;

use pocketmine\plugin\PluginBase;

use pocketmine\utils\Config;
use pocketmine\utils\TextFormat as Chat;

// >>>

use VGCore\chat\Filter;

use VGCore\listener\ChatFilterListener;

use VGCore\network\ModalFormRequestPacket;
use VGCore\network\ModalFormResponsePacket;
use VGCore\network\ServerSettingsRequestPacket;
use VGCore\network\ServerSettingsResponsePacket;

class SystemOS extends PluginBase {
    
    // Base File for arranging everything in good order. This is how every good core should be done. 
    
    public function onEnable() {
        $this->saveDefaultConfig();
        
        PacketPool::registerPacket(new ModalFormRequestPacket());
		PacketPool::registerPacket(new ModalFormResponsePacket());
		PacketPool::registerPacket(new ServerSettingsRequestPacket());
		PacketPool::registerPacket(new ServerSettingsResponsePacket());
        
        $this->getServer()->getPluginManager()->registerEvents(new ChatFilterListener($this), $this);
        $this->getServer()->getPluginManager()->registerEvents(new GUIListener($this), $this);
    }
}