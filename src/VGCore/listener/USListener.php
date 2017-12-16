<?php

namespace VGCore\listener;

use pocketmine\event\Event;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerJoinEvent;
// >>>
use VGCore\SystemOS;
use VGCore\user\UserSystem as US;

class USListener implements Listener {
    
    public $plugin;
    public $us;
    
    public function __construct(SystemOS $plugin) {
        $this->plugin = $plugin;
        $this->us = new US($this->plugin);
    }
    
    public function onJoin(PlayerJoinEvent $event) {
        $player = $event->getPlayer();
        $name = $player->getName();
        $this->us->makeUser($name);
        $bancheck = $this->us->isBan($name);
        if ($bancheck === true) {
            $banid = $this->getBanID($name);
            $player->close("", "§cYou are banned from the §dVGNetwork§c. Contact support to be unbanned. BAN ID : §e#" . $banid);
        }
    }
    
}