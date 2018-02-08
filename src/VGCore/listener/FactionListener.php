<?php

namespace VGCore\listener;

use pocketmine\event\{
    Listener,
    block\BlockPlaceEvent,
    player\PlayerChatEvent,
    player\PlayerJoinEvent,
    player\PlayerMoveEvent,
    entity\EntityDamageEvent
};

use pocketmine\utils\TextFormat as Chat;

use VGCore\math\Vector3 as Scaler;
// >>>
use VGCore\SystemOS;

use VGCore\faction\{
    FactionSystem as FS,
    FactionWar as FW
};

use VGCore\listener\event\{
    PreWarEvent
};

use VGCore\network\VGServer as VGS;

use VGCore\task\TaskManager;

class FactionListener implements Listener {

    /*
    Needs to be filled...
    */
    const RANDOM_LOCATION = [
        "x:y:z",
        "x:y:z",
        "x:y:z",
        "x:y:z",
        "x:y:z",
        "x:y:z",
        "x:y:z",
        "x:y:z",
        "x:y:z",
        "x:y:z",
        "x:y:z",
        "x:y:z",
        "x:y:z"
    ];
    
    private static $os;
    private static $ptp = [];
    private static $fp = 1;
    private static $timerun;
    private static $p = 0;
    
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
        $check = VGS::checkServer();
        if ($check !== 1) {
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
     * To stop players from moving until timer has ended.
     *
     * @param PlayerMoveEvent $event
     * @return void
     */
    public function onWarMove(PlayerMoveEvent $event) {
        $check = VGS::checkServer();
        if ($check !== 2) {
            return;
        }
        if (self::$timerun !== 2) {
            $player = $event->getPlayer();
            $player->sendMessage(Chat::RED . "Sorry, the war hasn't started yet. We'll let you know when it does.");
            $event->setCancelled(true);
        }
        return;
    }

    /**
     * To teleport players to correct locations to make a balanced war and check maxed out war players.
     *
     * @param PreWarEvent $event
     * @return void
     */
    public function onPreWar(PreWarEvent $event) {
        $max = FW::getMaxPlayer();
        if (self::$p >= $max) {
            return;
        }
        $location = array_rand(self::RANDOM_LOCATION, 1);
        if (count(self::$playertp > 0)) {
            foreach(self::$playertp as $s) {
                if ($location === $s) {
                    $this->onPreWar($event);
                    return;
                }
            }
        }
        $player = $event->getPlayer();
        list($x, $y, $z) = explode(":", $location);
        $scaler = new Scaler($x, $y, $z);
        $player->teleport($scaler);
        if (self::$fp !== 0) {
            self::$fp = 0;
            TaskManager::startTask("WarTimerTask");
        }
        self::$p = self::$p + 1;
        return;
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
        return;
    }

    /**
     * To set custom recipents per FactionChat.
     *
     * @param PlayerChatEvent $event
     * @return void
     */
    public function onFChat(PlayerChatEvent $event) {
        $player =  $event->getPlayer();
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

    /**
     * To check stuff related to faction on PlayerJoinEvent.
     *
     * @param PlayerJoinEvent $event
     * @return void
     */
    public function onJoin(PlayerJoinEvent $event) {
        $check = VGS::checkServer();
        if ($check === 2) {
            $player = $event->getPlayer();
            $server = self::$os->getServer();
            $pmanager = $server->getPluginManager();
            $swe = new PreWarEvent($player);
            $pmanager->callEvent($swe);
        }
        return;
    }
    
}