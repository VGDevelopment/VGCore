<?php

namespace VGCore\faction;

use pocketmine\Player;
// >>>
use VGCore\faction\FactionSystem;

use VGCore\user\UserSystem as US;

class FactionWar extends FactionSystem {
    
    private static $warip = [
        "i001.vgpe.me",
        "i002.vgpe.me"
    ];
    
    public static function startWar(array $faction): bool {
        $ip = self::getNonSessionIP();
        if ($ip === "All Sessions being used.") {
            return false;
        }
        $faction1 = $faction[0];
        $faction2 = $faction[1];
        $f1member = self::getAllFactionMember($faction1);
        $f2member = self::getAllFactionMember($faction2);
        if (count($f1member) < 3 || count($f2member) < 3) {
            return false;
        }
        $top = self::chooseTop($f1member, $f2member);
        $f1player = $top[0];
        $f2player = $top[1];
        $a1 = [];
        $a2 = [];
        foreach ($f1player as $i => $v) {
            if ($f1player[$i] === null) {
                $a1[] = null;
                continue;
            }
            if ($f2player[$i] === null) {
                $a2[] = null;
                continue;
            }
            $f1player[$i]->transfer($ip, 19832, "Transferring your faction's top players\n to a suitable location for WAR!");
            $a1[] = $f1player[$i];
            $f2player[$i]->transfer($ip, 19832, "Transferring your faction's top players\n to a suitable location for WAR!");
            $a2[] = $f2player[$i];
        }
        /*
        Should I setPlayerValueForNetwork() before transferring the player's for safe reach? @daniktheboss
        */
        self::setPlayerValueForNetwork($ip, $a1, $a2);
        self::turnSessionOn($ip);
        return true;
    }
    
    public static function getNonSessionIP(): string {
        foreach (self::$warip as $i => $v) {
            $query = self::$db->query("SELECT valid FROM wars WHERE serverip='" . self::$db->real_escape_string($v) . "'");
            $session = [];
            $session[$i] = $query->fetchArray()[0] ?? false;
        }
        $vskey = array_search(0, $session);
        if ($vskey !== false) {
            return self::$warip[$vskey];
        } else {
            return "All Sessions being used.";
        }
    }
    
    public static function turnSessionOn(string $ip): void {
        self::$db->query("UPDATE wars SET valid = 1 WHERE serverip='" . self::$db->real_escape_string($ip) . "'");
    }
    
    public static function turnSessionOff(string $ip): void {
        self::$db->query("UPDATE wars SET valid = 0 WHERE serverip='" . self::$db->real_escape_string($ip) . "'");
    }

    /**
     * Sets the player value to send to a war server.
     *
     * @param array $t1
     * @param array $t2
     * @return boolean
     */
    public static function setPlayerValueForNetwork(string $serverip, array $t1, array $t2): bool {
        /*
        0 => false,
        1 => true
        */
        $ut1 = self::formatPlayerNetworkArray($t1);
        $ut2 = self::formatPlayerNetworkArray($t2);
        $string = [
            implode(":", $ut1),
            implode(":", $ut2)
        ];
        $checklist = [];
        foreach($string as $param) {
            $query = self::$db->query("UPDATE wars SET t1 = '" . self::$db->real_escape_string($param) . "' WHERE serverip='" . self::$db->real_escape_string($serverip) . "'");
            if ($query === true) {
                $checklist[] = true;
                continue;
            }
            $checklist[] = false;
        }
        if ($checklist[0] === true && $checklist[1] === true) {
            return true;
        }
        return false;
    }

    /**
     * Formats the player array to 0s and 1s for setPlayerValueForNetwork()
     *
     * @param array $array
     * @return array
     */
    public static function formatPlayerNetworkArray(array $array): array {
        $basearray = [];
        foreach ($array as $i => $v) {
            if ($v === null) {
                $basearray[] = 0;
                continue;
            }
            $basearray[] = 1;
        }
        return $basearray;
    }
    
    public static function isWarOver(): bool {
        $online = self::$server->getOnlinePlayers();
        $alive = [];
        foreach ($online as $o) {
            if ($o->getGamemode() === 2) {
                $alive[] = $o;
            }
        }
        if (count($alive) > 1) {
            return false;
        } else {
            return true;
        }
    }
    
    public static function fallBack(array $player, string $winner): void {
        foreach ($player as $p) {
            if (self::getPlayerFaction($p) === strtolower($winner)) {
                $p->transfer("mc.vgpe.me", 19132, "WOOHOO! Good game!\nSending you back to lobby so you can have more fun!");
            } else {
                $p->transfer("mc.vgpe.me", 19132, "Yeah.. that wasn't your best performance.\nThere is always next time.\nSending you back to the lobby.");
            }
        }
    }
    
    public static function endWar(array $faction, int $result): bool {
        $ip = self::$server->getIp();
        self::turnSessionOff($ip);
        $check = self::isWarOver();
        if ($check === true) {
            $online = self::$server->getOnlinePlayers();
            $f1 = $faction[0];
            $f2 = $faction[1];
            if ($result === 1) {
                self::fallBack($online, $f1);
                return true;
            } else {
                self::fallBack($online, $f2);
                return true;
            }
        } 
    }
    
    public static function chooseTop(array $f1member, array $f2member): array {
        $data = [];
        $us = new US(self::$os);
        $allkdr1 = [];
        $allkdr2 = [];
        foreach ($f1member as $i => $v) {
            $name = $v->getName();
            $data = $us->userStat($name);
            $kdr = $data[0] / $data[1];
            $allkdr1[$i] = $kdr;
        }
        foreach ($f2member as $i => $v) {
            $name = $v->getName();
            $data = $us->userStat($name);
            $kdr = $data[0] / $data[1];
            $allkdr2[$i] = $kdr;
        }
        $f1player = [];
        $f2player = [];
        $i = 0;
        while ($i < 3) {
            $maxkdr1 = max($allkdr1);
            $key = array_search($maxkdr1, $allkdr1);
            $f1player[$i] = $f1member[$key];
            unset($allkdr1[$key]);
            $maxkdr2 = max($allkdr2);
            $key = array_search($maxkdr2, $allkdr2);
            $f2player[$i] = $f2member[$key];
            unset($allkdr2[$key]);
            $i + 1;
        }
        return [$f1player, $f2player];
    }
    
}