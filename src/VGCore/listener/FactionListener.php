<?php

namespace VGCore\listener;

use pocketmine\event\{
    Listener,
    block\BlockPlaceEvent,
    player\PlayerChatEvent
};

use pocketmine\utils\TextFormat as Chat;
// >>>
use VGCore\SystemOS;

use VGCore\faction\{
    FactionSystem as FS,
    FactionWar as FW
};

use VGCore\network\VGServer as VGS;

class FactionListener implements Listener {
    
    private static $os;
    
    public function __construct(SystemOS $os) {
        self::$os = $os;
    }
    
    public function onBuild(BlockPlaceEvent $event) {
        $server = new VGS(self::$os);
        $check = $server->checkServer();
        if ($check !== 3) {
            return;
        }
        $player = $event->getPlayer();
        $x = round($block->x);
        $z = round($block->z);
        $data = FS::getLand();
        if (count($data) <= 0) {
            return;
        }
        foreach ($data as $i => $v) {
            list($x1, $z1) = explode(":", $v[0], 2);
            list($x2, $z2) = explode(":", $v[1], 2);
            if ($x < $x1 && $x > $x2) {
                if ($z < $z1 && $z > $z2) {
                    $event->setCancelled(true);
                }
            }
        }
        $check = FS::inFaction($player);
        if ($check === true) {
            $faction = FS::getPlayerFaction($player);
            $data = FS::getLand($faction);
            foreach ($data as $i => $v) {
                list($x1, $z1) = explode(":", $v[0], 2);
                list($x2, $z2) = explode(":", $v[1], 2);
                if ($x <= $x1 && $x >= $x2) {
                    if ($z <= $z1 && $z >= $z2) {
                        $event->setCancelled(false);
                        return;
                    }
                } else {
                    $player->sendMessage(Chat::RED . "Sorry, you've can't build in a land claimed by any other faction. Please understand.");
                }
            }
        } else {
            $player->sendMessage(Chat::RED . "Sorry, you've can't build in a land claimed by any other faction. Please understand.");
            return;
        }
    }
    
    public function onChat(PlayerChatEvent $event) {
        $player = $event->getPlayer();
        $check = FS::inFaction($player);
        if ($check === true) {
            $message = $event->getMessage();
            $player = $event->getPlayer();
            $faction = FS::getPlayerFaction($player);
            $newmessage = Chat::GREEN . "<" . Chat::RED . $faction . Chat::GREEN . "> " . Chat::RESET . $message;
            $event->setMessage($newmessage);
        }
    }
    
}