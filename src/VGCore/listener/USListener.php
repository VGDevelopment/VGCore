<?php

namespace VGCore\listener;

use pocketmine\event\Event;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerJoinEvent;

use pocketmine\network\mcpe\protocol\GameRulesChangedPacket as GMRCPacket;
// >>>
use VGCore\SystemOS;

use VGCore\user\UserSystem as US;

use VGCore\ban\BanSystem as BS;

use VGCore\economy\EconomySystem as ES;

use VGCore\network\Database as DB;

use VGCore\lobby\crate\Crate;

class USListener implements Listener {
    
    public $plugin;
    public $us;
    public $bs;
    public $es;
    
    public function __construct(SystemOS $plugin) {
        $this->plugin = $plugin;
        $this->us = new US($this->plugin);
        $this->bs = new BS($this->plugin);
        $this->es = new ES($this->plugin);
    }
    
    public function onJoin(PlayerJoinEvent $event) {
        $player = $event->getPlayer();
        $name = $player->getName();
        $xuid = $player->getXuid();
        if ($this->us->isUserNew($xuid) === false) {
            $this->us->updateUserID($xuid, $name);
            return;
        }
        DB::createUser($name, $xuid);
        $bancheck = $this->bs->isBan($name);
        if ($bancheck === true) {
            $banid = $this->bs->getBanID($name);
            $player->close("", "§cYou are banned from the §dVGNetwork§c. Appeal by emailing §asupport@vgpe.me§c - BAN ID : §e#" . $banid);
        }
        /*
        Sets show Coordinates to true in GameRules.
        */
        $pk = new GMRCPacket();
        $pk->gamerules["showcoordinates"] = [1, true];
        $player->dataPacket($pk);
    }
    
}