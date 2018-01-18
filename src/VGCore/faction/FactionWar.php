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
        $top = self::chooseTop($f1member, $f2member);
        $f1player = $top[0];
        $f2player = $top[1];
        foreach ($f1player as $i => $v) {
            $f1player[$i]->transfer($ip, 19832, "Transferring your faction's top players\n to a suitable location for WAR!");
            $f2player[$i]->transfer($ip, 19832, "Transferring your faction's top players\n to a suitable location for WAR!");
        }
        self::turnSessionOn($ip);
        return true;
    }
    
    public static function getNonSessionIP(): string {
        foreach (self::$warip as $i => $v) {
            $query = self::$db->query("SELECT session FROM wars WHERE serverip='" . self::$db->real_escape_string($v) . "'");
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
        self::$db->query("UPDATE wars SET session = 1 WHERE serverip='" . self::$db->real_escape_string($ip) . "'");
    }
    
    public static function turnSessionOff(string $ip): void {
        self::$db->query("UPDATE wars SET session = 0 WHERE serverip='" . self::$db->real_escape_string($ip) . "'");
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
                $p->transfer("mc.vgpe.me", 19132, "Yeah.. that wasn't your best performance.\nThere is always next time.\n Sending you back to the lobby.");
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