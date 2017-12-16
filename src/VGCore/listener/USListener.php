<?php

namespace VGCore\listener;

use pocketmine\event\Event;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerJoinEvent;
// >>>
use VGCore\SystemOS;
use VGCore\user\UserSystem as US;
use VGCore\ban\BanSystem as BS;

class USListener implements Listener {
    
    public $plugin;
    public $us;
    
    public function __construct(SystemOS $plugin) {
        $this->plugin = $plugin;
        $this->us = new US($this->plugin);
        $this->bs = new BS($this->plugin);
    }
    
    public function onJoin(PlayerJoinEvent $event) {
        $player = $event->getPlayer();
        $name = $player->getName();
        $this->us->makeUser($name);
        $bancheck = $this->bs->isBan($name);
        if ($bancheck === true) {
            $banid = $this->bs->getBanID($name);
            $player->close("", "§cYou are banned from the §dVGNetwork§c. Appeal by emailing §asupport@vgpe.me§c - BAN ID : §e#" . $banid);
        }
    }
    
}