<?php

namespace VGCore\network;

use VGCore\SystemOS;

class VGServer {
    
    private static $lobby = [19132];
    private static $faction = [19283];
    private static $factionwar = [19832, 19833];
    
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
    public static function checkServer(SystemOS $os): int {
        $port = $os->getServer()->getPort();
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
    
}