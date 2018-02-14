<?php

namespace VGCore\user\manager\ban;

use pocketmine\Player;
// >>>
use VGCore\SystemOS;

use VGCore\user\{
    UserOS,
    Staff,
    manager\UserSystem
};

use VGCore\network\Database;

class BanSystem extends UserOS implements Staff {

    const DEFAULT_BAN_REASON = "BREAKING RULES";
    
    private static $db;
    private static $os;

    /**
     * Starts up the ban management system.
     *
     * @param SystemOS $os
     * @param mixed $db
     * @return void
     */
    protected static function start(SystemOS $os, mixed $db): void {
        self::$os = $os;
        self::$db = $db;
    }

    /**
     * Bans the user based on name, reason, banner (admin), and a banID.
     *
     * @param string $name
     * @param string $reason
     * @param string $admin
     * @param string $banid
     * @return boolean
     */
    private static function banUser(string $name, string $reason, string $admin, string $banid): bool {
        $check = Database::checkUser($name);
        if ($check === true) {
            $lowername = strtolower($name);
            if ($check === true) {
                
            }
        }
    }

    /**
     * Checks whether user has been banned.
     *
     * @param string $name
     * @return boolean
     */
    public static function banCheck(string $name): bool {
        $check = Database::checkUser($name);
        if ($check === false) {
            return false;
        }
        $query = self::$db->query("SELECT ban FROM users WHERE username='" . self::$db->real_escape_string . "'");
        $result = $query->fetchArray();
        $query->free();
        if ($result[0] === null) {
            return false;
        }
        return true;
    }

    /**
     * Generates a BanID.
     *
     * @return string
     */
    private static function generateBanID(): string {
        $query = self::$db->query("SELECT banid FROM users");
        $allbanid = $query->fetchArray();
        if ($allbanid[0] === null) {
            $raw = 1;
            $id = sprintf("%05d", $raw);
            return (string)$id;
        }
        $max = max($allbanid);
        $raw = $max + 1;
        $id = sprintf("%05d", $raw);
        return (string)$id;
    }

    /**
     * Bans users based on Player Objects (player & admin) and a reason. Generates a ban id via generateBanID().
     *
     * @param Player $player
     * @param Player $admin
     * @param string $reason
     * @return boolean
     */
    public static function banPlayer(Player $player, Player $admin, string $reason = self::DEFAULT_BAN_REASON): bool {
        $name = $player->getName();
        $adname = $admin->getName();
        $lowarray = [
            "name" => strtolower($name),
            "admin" => strtolower($adname)
        ];
        $id = self::generateBanID();
        self::banUser($lowarray["name"], $reason, $lowarray["admin"], $id);
    }

    /**
     * Gets the ban ID for the specific banned player, for support/appeals. 
     *
     * @param string $name
     * @return string
     */
    public static function getBanID(string $name): string {
        $check = [
            "exist" => Database::checkUser($name),
            "isban" => self::banCheck($name)
        ];
        if ($check["exist"] === true && $check["isban"] === true) {
            $lowername =  strtolower($name);
            $query = self::$db->query("SELECT banid FROM users WHERE username='" . self::$db->real_escape_string($lowername) . "'");
            $result = $query->fetchArray();
            $query->free();
            if ($result[0] === null) {
                return "ERROR";
            }
            return $result[0];
        }
        return "User doesn't exist or hasn't been banned.";
    }

    private static function setBan(string $name, bool $pointer = true): bool {
        $bit = $pointer ? 1 : 0;
        self::$db->query("UPDATE users SET ban = " . $bit . " WHERE username='" . self::$db->real_escape_string($name) . "'");
        return true;
    }
    
}