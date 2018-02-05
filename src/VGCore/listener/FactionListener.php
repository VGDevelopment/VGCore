<?php

namespace VGCore\listener;

use pocketmine\event\{
    Listener,
    block\BlockPlaceEvent,
    player\PlayerChatEvent,
    entity\EntityDamageEvent
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
    
    /**
     * To stop players from building on other player's claimed lands.
     *
     * @param BlockPlaceEvent $event
     * @return void
     */
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

    public function onKill(EntityDamageEvent $event) {
        
    }
    
    /**
     * To add the <faction> prefix to chat messages.
     *
     * @param PlayerChatEvent $event
     * @return void
     */
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

    /**
     * To set custom recipents per FactionChat.
     *
     * @param PlayerChatEvent $event
     * @return void
     */
    public function onFChat(PlayerChatEvent $event) {
        $player = $event->getPlayer();
        $check = FS::inFaction($player);
        if ($check === true) {
            $name = $player->getName();
            $lowername = strtolower($name);
            if (array_key_exists($lowername, FS::$fchat)) {
                if (FS::$fchat[$lowername] === true) {
                    $faction = FS::getPlayerFaction($player);
                    $memberlist = FS::getAllFactionMember($faction);
                    /*
                    To stop duplication of message when sending. Recipents also include the player itself which is set by PM by default. Thus, it would duplicate the message in the player chat log.
                    */
                    if (in_array($player, $memberlist)) {
                        $i = array_search($player, $memberlist);
                        unset($memberlist[$i]);
                    }
                    $event->setRecipients($memberlist);
                    return;
                }
            }
        }
        return;
    }
    
}