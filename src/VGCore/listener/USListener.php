<?php

namespace VGCore\listener;

use pocketmine\event\Event;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerJoinEvent;

use pocketmine\network\mcpe\protocol\GameRulesChangedPacket as GMRCPacket;
// >>>
use VGCore\SystemOS;

use VGCore\user\manager\{
    UserSystem,
    ban\BanSystem
};

use VGCore\economy\EconomySystem as ES;

use VGCore\network\Database as DB;

use VGCore\user\Staff;

class USListener implements Listener {

    private static $plugin;

    public function __construct(SystemOS $plugin) {
        self::$plugin = $plugin;
    }

    public function onJoin(PlayerJoinEvent $event) {
        $player = $event->getPlayer();
        $name = $player->getName();
        $xuid = $player->getXuid();
        if (UserSystem::checkIfUserIDExist($xuid) === false) {
            UserSystem::updateUserPerUserID($xuid, $name);
            return;
        }
        UserSystem::createUser($name, $xuid);
        $bancheck = BanSystem::banCheck($name);
        if ($bancheck === true) {
            $banid = BanSystem::getBanID($name);
            $player->close("", "§cYou are banned from the §dVGNetwork§c. Appeal by emailing §asupport@vgpe.me§c - BAN ID : §e#" . $banid);
        }
        /*
        Set player rank to staff if he belongs in staff.
        */
        $lname = strtolower($name);
        if (in_array($lname, Staff::STAFF)) {
            UserSystem::setRank($name, UserSystem::R6);
        }
        /*
        Sets show Coordinates to true in GameRules.
        */
        UserSystem::allowPositionView($player);
    }



}
