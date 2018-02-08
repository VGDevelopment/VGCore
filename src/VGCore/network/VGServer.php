<?php

namespace VGCore\network;

use VGCore\SystemOS;

class VGServer {
    
    private static $lobby = [19132];
    private static $faction = [19283];
    private static $factionwar = [
        1 => 19832,
        2 => 19833
    ];
    private static $os;

    public static function start(SystemOS $os): void {
        self::$os = $os;
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

    public static function selectWarServer(): string {
        $check = self::checkServer();
        if ($check === 2) {
            $port = self::$os->getServer()->getPort();
            $key = array_search($port, self::$factionwar);
            if ($key === 1) {
                return "iwar1.vgpe.me";
            } else if ($key === 2) {
                return "iwar2.vgpe.me";
            }
        }
    }
    
}