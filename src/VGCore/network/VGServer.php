<?php

namespace VGCore\network;

use VGCore\SystemOS;

class VGServer {

    /**
     * @package MOTD
     */
    const MOTD = [
        "Lobby" => "§aVirtualGalaxy §c[ALPHA]",
        "Faction" => "§aImage 001",
        "War" => [
            0 => "§aImage 002",
            1 => "§aImage 003"
        ]
    ];
    
    private static $lobby = [19132];
    private static $faction = [19283];
    private static $factionwar = [
        1 => 19832,
        2 => 19833
    ];
    private static $os;
    private static $raklibnetwork;

    public static function start(SystemOS $os): bool {
        self::$os = $os;
        self::$raklibnetwork = $os->getServer()->getNetwork();
        $motd = self::setMOTD();
        if ($motd === true) {
            return true;
        } else {
            return false;
        }
    }
    
    public static function getLobby(): array {
        return self::$lobby;
    }
    
    public static function getFaction(): array {
        return self::$faction;
    }
    
    public static function getFactionWar(): array {
        return self::$factionwar;
    }
    
    /**
     * Checks the server port and matches it with static arrays above. The matched value gives the correct integer return.
     *
     * @param SystemOS $os
     * @return integer
     */
    public static function checkServer(): int {
        $port = self::$os->getServer()->getPort();
        if (in_array($port, self::$lobby)) {
            return 0;
        } else if (in_array($port, self::$faction)) {
            return 1;
        } else if (in_array($port, self::$factionwar)) {
            return 2;
        } else {
            return 999;
        }
    }

    public static function selectWarServer(): int {
        $check = self::checkServer();
        if ($check === 2) {
            $port = self::$os->getServer()->getPort();
            $key = array_search($port, self::$factionwar);
            if ($key === 1) {
                return 0;
            } else if ($key === 2) {
                return 1;
            }
        }
    }

    /**
     * Sets the server MOTD and returns how it went.
     *
     * @return boolean
     */
    private static function setMOTD(): bool {
        $server = self::checkServer();
        if ($server === 0) {
            self::$raklibnetwork->setName(self::MOTD["Lobby"]);
            return true;
        } else if ($server === 1) {
            self::$raklibnetwork->setName(self::MOTD["Faction"]);
            return true;
        } else if ($server === 2) {
            $server = self::selectWarServer();
            self::$raklibnetwork->setName(self::MOTD["War"][$server]);
            return true;
        }
        return false;
    }
    
}