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
        $point = (string)$x . ", " . (string)$z;
        foreach ($data as $i => $v) {
            if ($v[0] === "") {
                continue;
            }
            list($x1, $z1) = explode(":", $v[0]);
            list($x2, $z2) = explode(":", $v[1]);
            $graph = [
                "x1" => $x1,
                "x2" => $x2,
                "z1" => $z1,
                "z2" => $z2
            ];
            $check = FS::pointInGraph($point, $graph);
            if ($check === true) {
                $event->setCancelled(true);
            }
        }
        $check = FS::inFaction($player);
        if ($check === true) {
            $faction = FS::getPlayerFaction($player);
            $data = FS::getLand($faction);
            foreach ($data as $i => $v) {
                if ($v[0] === "") {
                    continue;
                }
                list($x1, $z1) = explode(":", $v[0]);
                list($x2, $z2) = explode(":", $v[1]);
                $graph = [
                    "x1" => $x1,
                    "x2" => $x2,
                    "z1" => $z1,
                    "z2" => $z2
                ];
                $check = FS::pointInGraph($point, $graph);
                if ($check === true) {
                    $event->setCancelled(false);
                }
            }
        } else {
            $player->sendMessage(Chat::RED . "Sorry, you've can't build in a land claimed by any other faction. Please understand.");
            return;
        }
        return;
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